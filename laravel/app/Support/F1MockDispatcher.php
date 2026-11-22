<?php

namespace App\Support;

use App\Models\Generation;
use App\Models\GenerationAttempt;
use App\Models\GenerationOutbox;
use App\Models\GenerationReopen;
use App\Support\DesignAgent\DesignInvokeStub;
use App\Support\DesignAgent\InvokeClassification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Applies one stub classification after the outbox row has been committed.
 * It does not create a retry attempt and it does not call W2.
 */
class F1MockDispatcher
{
    public function apply(Generation $generation, GenerationAttempt $attempt, InvokeClassification $classification): DesignCallbackResult
    {
        if ($classification->name === InvokeClassification::CONFIRMED_NOT_SENT && ! $classification->positiveProofNotSent) {
            $classification = InvokeClassification::unknown();
        }

        if ($classification->name === InvokeClassification::ACCEPTED_202 && ($classification->httpStatus !== 202 || ! is_string($classification->jobId) || $classification->jobId === '')) {
            $classification = InvokeClassification::unknown();
        }

        if ($classification->name === InvokeClassification::REJECTED_400 && ($classification->httpStatus !== 400 || $classification->errorCode !== 'invalid_input')) {
            $classification = new InvokeClassification(InvokeClassification::REJECTED_OTHER, $classification->httpStatus);
        }

        return match ($classification->name) {
            InvokeClassification::ACCEPTED_202 => $this->accepted($generation, $attempt, $classification),
            InvokeClassification::REJECTED_400 => $this->rejected400($generation, $attempt, $classification),
            InvokeClassification::REJECTED_OTHER => $this->rejectedOther($generation, $attempt, $classification),
            InvokeClassification::CONFIRMED_NOT_SENT => $this->notSent($generation, $attempt),
            default => $this->unknown($generation, $attempt, $classification),
        };
    }

    public function recordUnknown(Generation $generation, GenerationAttempt $attempt): DesignCallbackResult
    {
        return $this->unknown($generation, $attempt, InvokeClassification::unknown());
    }

    /**
     * Send the same attempt again only after positive proof that no bytes left.
     *
     * @param  array<string, mixed>  $payload
     */
    public function resend(GenerationAttempt $attempt, DesignInvokeStub $stub, array $payload): DesignCallbackResult
    {
        $outbox = GenerationOutbox::query()->where('attempt_id', $attempt->id)->first();
        if ($outbox === null || $outbox->state !== 'confirmed_not_sent' || $attempt->status !== 'pending_invoke') {
            return new DesignCallbackResult(409, [
                'received' => false,
                'error_code' => 'resend_not_allowed',
                'message' => 'Resend stays on the same attempt and only follows confirmed_not_sent.',
            ]);
        }

        $before = GenerationAttempt::query()->count();
        $outbox->state = 'pending';
        $outbox->delivery_classification = null;
        $outbox->dead_at = null;
        $outbox->save();

        $generation = $attempt->generation()->firstOrFail();
        $result = $this->apply($generation, $attempt->fresh() ?? $attempt, $stub->classify($payload));

        if (GenerationAttempt::query()->count() !== $before) {
            throw new \RuntimeException('Resend created an attempt.');
        }

        return $result;
    }

    /**
     * Explicit operational timeout. No duration is stored or assumed.
     * The parent becomes invoke_unconfirmed. The attempt becomes ambiguous.
     */
    public function recordOperationalUnconfirmed(Generation $generation): DesignCallbackResult
    {
        return DB::transaction(function () use ($generation): DesignCallbackResult {
            $generation = Generation::query()->whereKey($generation->id)->lockForUpdate()->firstOrFail();
            $attempt = $generation->current_attempt_id === null
                ? null
                : GenerationAttempt::query()->whereKey($generation->current_attempt_id)->lockForUpdate()->first();

            if ($attempt !== null && ! $attempt->isTerminal()) {
                $attempt->status = 'ambiguous';
                $attempt->save();
                DesignAudit::record('attempt_ambiguous', [
                    'project_id' => $generation->project_id,
                    'request_id' => $generation->request_id,
                ], $generation->id, $attempt->id);
            }

            $generation->status = 'invoke_unconfirmed';
            $generation->save();

            return new DesignCallbackResult(200, [
                'received' => true,
                'applied' => false,
                'generation_id' => $generation->id,
                'request_id' => $generation->request_id,
                'status' => 'invoke_unconfirmed',
                'attempt_status' => $attempt?->status,
            ]);
        });
    }

    /**
     * Explicit audited reopen. This is not an automatic retry.
     */
    public function reopen(Generation $generation, string $actor, string $reason, bool $acknowledgeDuplicateRisk): DesignCallbackResult
    {
        if (! $acknowledgeDuplicateRisk || trim($actor) === '' || trim($reason) === '') {
            return new DesignCallbackResult(422, [
                'received' => false,
                'error_code' => 'reopen_not_acknowledged',
                'message' => 'Manual reopen requires an actor, a reason, and acknowledgement of duplicate processing.',
            ]);
        }

        return DB::transaction(function () use ($generation, $actor, $reason): DesignCallbackResult {
            $generation = Generation::query()->whereKey($generation->id)->lockForUpdate()->firstOrFail();
            $previous = $generation->current_attempt_id === null
                ? null
                : GenerationAttempt::query()->whereKey($generation->current_attempt_id)->lockForUpdate()->first();

            if ($previous === null || ($previous->status !== 'ambiguous' && $generation->status !== 'invoke_unconfirmed')) {
                return new DesignCallbackResult(409, [
                    'received' => false,
                    'error_code' => 'reopen_not_allowed',
                    'message' => 'Manual reopen starts from an ambiguous attempt or an invoke_unconfirmed parent.',
                ]);
            }

            $previous->status = 'superseded';
            $previous->save();

            $newId = (string) Str::uuid();
            $created = GenerationAttempt::query()->create([
                'id' => $newId,
                'generation_id' => $generation->id,
                'job_id' => null,
                'attempt_number' => $previous->attempt_number + 1,
                'status' => 'pending_invoke',
                'invoke_correlation_key' => null,
            ]);

            $previousOutbox = GenerationOutbox::query()->where('attempt_id', $previous->id)->first();
            GenerationOutbox::query()->create([
                'id' => (string) Str::uuid(),
                'attempt_id' => $created->id,
                'state' => 'pending',
                'body' => $previousOutbox?->body ?? new \stdClass,
            ]);

            $generation->current_attempt_id = $created->id;
            $generation->status = 'pending_invoke';
            $generation->save();

            GenerationReopen::query()->create([
                'id' => (string) Str::uuid(),
                'generation_id' => $generation->id,
                'previous_attempt_id' => $previous->id,
                'new_attempt_id' => $created->id,
                'actor' => $actor,
                'reason' => $reason,
                'duplicate_risk_acknowledged' => true,
                'created_at' => CarbonImmutable::now('UTC'),
            ]);

            DesignAudit::record('manual_reopen', [
                'actor' => $actor,
                'previous_attempt_id' => $previous->id,
                'new_attempt_id' => $created->id,
            ], $generation->id, $created->id);

            return new DesignCallbackResult(200, [
                'received' => true,
                'applied' => false,
                'generation_id' => $generation->id,
                'request_id' => $generation->request_id,
                'attempt_id' => $created->id,
                'status' => 'pending_invoke',
            ]);
        });
    }

    private function accepted(Generation $generation, GenerationAttempt $attempt, InvokeClassification $classification): DesignCallbackResult
    {
        return DB::transaction(function () use ($generation, $attempt, $classification): DesignCallbackResult {
            [$generation, $attempt, $outbox] = $this->lock($generation, $attempt);
            $now = CarbonImmutable::now('UTC');

            $attempt->status = 'accepted';
            $attempt->job_id = $classification->jobId;
            $attempt->http_status = 202;
            $attempt->accepted_at = $now;
            $attempt->save();

            $generation->status = 'accepted';
            $generation->accepted_at = $now;
            $generation->save();

            $this->closeOutbox($outbox, 'accepted_202', 202, $now);
            DesignAudit::record('attempt_accepted', [
                'job_id' => $classification->jobId,
                'project_id' => $generation->project_id,
                'request_id' => $generation->request_id,
            ], $generation->id, $attempt->id, $outbox->id);

            return new DesignCallbackResult(202, [
                'received' => true,
                'applied' => false,
                'delivery_classification' => 'accepted_202',
                'generation_id' => $generation->id,
                'project_id' => $generation->project_id,
                'request_id' => $generation->request_id,
                'job_id' => $classification->jobId,
                'status' => 'accepted',
            ]);
        });
    }

    private function rejected400(Generation $generation, GenerationAttempt $attempt, InvokeClassification $classification): DesignCallbackResult
    {
        return DB::transaction(function () use ($generation, $attempt, $classification): DesignCallbackResult {
            [$generation, $attempt, $outbox] = $this->lock($generation, $attempt);
            $now = CarbonImmutable::now('UTC');

            $attempt->status = 'failed';
            $attempt->http_status = 400;
            $attempt->error_code = 'invalid_input';
            $attempt->error_message = $classification->message;
            $attempt->error_details = $classification->details;
            $attempt->retryable = false;
            $attempt->completed_at = $now;
            $attempt->save();

            $generation->status = 'failed';
            $generation->completed_at = $now;
            $generation->save();

            $this->closeOutbox($outbox, 'rejected_400', 400, $now);
            DesignAudit::record('attempt_rejected_invalid_input', [
                'project_id' => $generation->project_id,
                'request_id' => $generation->request_id,
            ], $generation->id, $attempt->id, $outbox->id);

            return new DesignCallbackResult(400, [
                'error_code' => 'invalid_input',
                'http_status' => 400,
                'details' => (object) ($classification->details ?? []),
                'request_id' => $generation->request_id,
                'project_id' => $generation->project_id,
                'retryable' => false,
            ]);
        });
    }

    private function rejectedOther(Generation $generation, GenerationAttempt $attempt, InvokeClassification $classification): DesignCallbackResult
    {
        return DB::transaction(function () use ($generation, $attempt, $classification): DesignCallbackResult {
            [$generation, $attempt, $outbox] = $this->lock($generation, $attempt);
            $now = CarbonImmutable::now('UTC');

            $attempt->status = 'invoking';
            $attempt->http_status = $classification->httpStatus;
            $attempt->save();

            $generation->status = 'invoking';
            $generation->save();

            $this->closeOutbox($outbox, 'rejected_other', $classification->httpStatus, $now);
            DesignAudit::record('delivery_rejected_other', [
                'http_status' => $classification->httpStatus,
                'project_id' => $generation->project_id,
                'request_id' => $generation->request_id,
            ], $generation->id, $attempt->id, $outbox->id);

            return new DesignCallbackResult(200, [
                'received' => true,
                'applied' => false,
                'delivery_classification' => 'rejected_other',
                'http_status' => $classification->httpStatus,
                'generation_id' => $generation->id,
                'request_id' => $generation->request_id,
                'status' => 'invoking',
            ]);
        });
    }

    private function notSent(Generation $generation, GenerationAttempt $attempt): DesignCallbackResult
    {
        return DB::transaction(function () use ($generation, $attempt): DesignCallbackResult {
            [$generation, $attempt, $outbox] = $this->lock($generation, $attempt);

            $attempt->status = 'pending_invoke';
            $attempt->save();

            $generation->status = 'pending_invoke';
            $generation->save();

            $outbox->state = 'confirmed_not_sent';
            $outbox->delivery_classification = 'confirmed_not_sent';
            $outbox->http_status = null;
            $outbox->dead_at = null;
            $outbox->save();

            DesignAudit::record('outbox_confirmed_not_sent', [
                'project_id' => $generation->project_id,
                'request_id' => $generation->request_id,
            ], $generation->id, $attempt->id, $outbox->id);

            return new DesignCallbackResult(200, [
                'received' => true,
                'applied' => false,
                'delivery_classification' => 'confirmed_not_sent',
                'generation_id' => $generation->id,
                'request_id' => $generation->request_id,
                'status' => 'pending_invoke',
            ]);
        });
    }

    private function unknown(Generation $generation, GenerationAttempt $attempt, InvokeClassification $classification): DesignCallbackResult
    {
        return DB::transaction(function () use ($generation, $attempt, $classification): DesignCallbackResult {
            [$generation, $attempt, $outbox] = $this->lock($generation, $attempt);

            $attempt->status = 'ambiguous';
            $attempt->http_status = $classification->httpStatus;
            $attempt->save();

            $generation->status = 'invoking';
            $generation->save();

            $outbox->state = 'unknown';
            $outbox->delivery_classification = 'unknown';
            $outbox->http_status = $classification->httpStatus;
            $outbox->dead_at = null;
            $outbox->save();

            DesignAudit::record('delivery_unknown', [
                'project_id' => $generation->project_id,
                'request_id' => $generation->request_id,
            ], $generation->id, $attempt->id, $outbox->id);
            DesignAudit::record('attempt_ambiguous', [
                'project_id' => $generation->project_id,
                'request_id' => $generation->request_id,
            ], $generation->id, $attempt->id, $outbox->id);

            return new DesignCallbackResult(200, [
                'received' => true,
                'applied' => false,
                'delivery_classification' => 'unknown',
                'generation_id' => $generation->id,
                'request_id' => $generation->request_id,
                'status' => 'invoking',
                'attempt_status' => 'ambiguous',
            ]);
        });
    }

    /**
     * @return array{0: Generation, 1: GenerationAttempt, 2: GenerationOutbox}
     */
    private function lock(Generation $generation, GenerationAttempt $attempt): array
    {
        $generation = Generation::query()->whereKey($generation->id)->lockForUpdate()->firstOrFail();
        $attempt = GenerationAttempt::query()->whereKey($attempt->id)->lockForUpdate()->firstOrFail();
        $outbox = GenerationOutbox::query()->where('attempt_id', $attempt->id)->lockForUpdate()->firstOrFail();

        return [$generation, $attempt, $outbox];
    }

    private function closeOutbox(GenerationOutbox $outbox, string $classification, ?int $httpStatus, CarbonImmutable $now): void
    {
        $outbox->state = 'dead';
        $outbox->delivery_classification = $classification;
        $outbox->http_status = $httpStatus;
        $outbox->dead_at = $now;
        $outbox->save();
    }
}
