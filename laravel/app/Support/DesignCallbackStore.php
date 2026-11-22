<?php

namespace App\Support;

use App\Models\Generation;
use App\Models\GenerationAttempt;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lock order is always generation, then attempt. A callback never inserts a row
 * and never locks an attempt from another generation.
 *
 * A terminal attempt with a null callback_payload_hash is treated as a conflict.
 * The hash is the only proof that a repeat is identical, so a missing hash is not
 * accepted as a duplicate.
 *
 * completed, expired, and failed generations are terminal. A callback may
 * still record a non-terminal attempt, but it must not change that generation.
 */
class DesignCallbackStore
{
    /**
     * @var list<string>
     */
    private const TERMINAL_GENERATION_STATUSES = ['completed', 'expired', 'failed'];

    public const JOB_ID_UNIQUE_INDEX = 'generation_attempts_job_id_unique';
    public static function record(DesignCallbackData $callback, string $hash): DesignCallbackResult
    {
        if ($callback->attemptId !== null) {
            $named = GenerationAttempt::query()->whereKey($callback->attemptId)->first();
            if ($named !== null && $named->job_id !== null && $named->job_id !== $callback->jobId) {
                return self::conflict('attempt is already bound to a different job_id.');
            }
        }

        $located = GenerationAttempt::query()->where('job_id', $callback->jobId)->first(['id', 'generation_id']);
        if ($located === null) {
            return self::unmatched();
        }

        if ($callback->attemptId !== null && strcasecmp($callback->attemptId, (string) $located->id) !== 0) {
            return self::conflict('job_id is already assigned to another attempt.');
        }

        return DB::transaction(function () use ($located, $callback, $hash): DesignCallbackResult {
            $generation = Generation::query()->whereKey($located->generation_id)->lockForUpdate()->first();
            if ($generation === null) {
                return self::notFound();
            }

            $attempt = GenerationAttempt::query()->whereKey($located->id)->lockForUpdate()->first();
            if ($attempt === null) {
                return self::notFound();
            }

            if (strcasecmp((string) $attempt->generation_id, (string) $generation->id) !== 0) {
                return self::conflict('Attempt does not belong to this generation.');
            }

            if (
                ($callback->generationId !== null && strcasecmp($callback->generationId, (string) $generation->id) !== 0)
                || $callback->projectId !== $generation->project_id
                || $callback->requestId !== $generation->request_id
                || ($callback->attemptId !== null && strcasecmp($callback->attemptId, (string) $attempt->id) !== 0)
            ) {
                return self::conflict('Callback identity does not match the generation.');
            }

            if ($attempt->job_id !== null && $attempt->job_id !== $callback->jobId) {
                return self::conflict('attempt is already bound to a different job_id.');
            }

            $isCurrent = $generation->current_attempt_id !== null
                && strcasecmp((string) $generation->current_attempt_id, (string) $attempt->id) === 0;

            if (! $isCurrent) {
                Log::info('design_callback_recorded', [
                    'project_id' => $generation->project_id,
                    'request_id' => $generation->request_id,
                    'generation_id' => $generation->id,
                    'attempt_id' => $attempt->id,
                    'job_id' => $attempt->job_id,
                    'applied' => false,
                    'reason' => 'stale_attempt',
                    'status' => $generation->status,
                ]);
                DesignAudit::record('callback_stale', [
                    'job_id' => $callback->jobId,
                    'project_id' => $generation->project_id,
                    'request_id' => $generation->request_id,
                ], $generation->id, $attempt->id);

                return self::received(false, 'stale_attempt', $generation, $attempt);
            }

            if ($attempt->isTerminal()) {
                $stored = $attempt->callback_payload_hash;
                if (is_string($stored) && $stored !== '' && hash_equals($stored, $hash)) {
                    DesignAudit::record('callback_duplicate', [
                        'job_id' => $callback->jobId,
                        'match' => 'payload_hash',
                    ], $generation->id, $attempt->id);

                    return self::received(false, 'duplicate', $generation, $attempt);
                }

                return self::conflict('Terminal attempt received a different callback payload.');
            }

            if ($attempt->status !== 'accepted') {
                DesignAudit::record('callback_current_attempt_non_accepted', [
                    'job_id' => $callback->jobId,
                    'attempt_status' => $attempt->status,
                ], $generation->id, $attempt->id);

                return self::received(false, 'current_attempt_non_accepted', $generation, $attempt);
            }

            self::applyAttempt($attempt, $callback, $hash);

            $generationIsTerminal = in_array($generation->status, self::TERMINAL_GENERATION_STATUSES, true);

            if (! $generationIsTerminal) {
                self::applyGeneration($generation, $callback);
            }

            $reason = null;
            $applied = ! $generationIsTerminal;
            if (! $applied) {
                $reason = 'generation_terminal';
            }

            DesignAudit::record($callback->status === 'ok' ? 'callback_matched' : 'callback_matched_error', [
                'job_id' => $callback->jobId,
                'retryable' => $callback->retryable,
                'applied' => $applied,
            ], $generation->id, $attempt->id);

            Log::info('design_callback_recorded', [
                'project_id' => $generation->project_id,
                'request_id' => $generation->request_id,
                'generation_id' => $generation->id,
                'attempt_id' => $attempt->id,
                'job_id' => $attempt->job_id,
                'applied' => $applied,
                'reason' => $reason,
                'status' => $generation->status,
            ]);

            return self::received($applied, $reason, $generation, $attempt);
        });
    }

    /**
     * True only for the partial unique index generation_attempts_job_id_unique.
     *
     * PostgreSQL reports SQLSTATE 23505 and the index name. SQLite reports
     * SQLSTATE 23000 and the column of that same index, not the index name.
     */
    public static function isJobIdConflict(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $message = $exception->getMessage();

        if ($sqlState === '23505' && str_contains($message, self::JOB_ID_UNIQUE_INDEX)) {
            return true;
        }

        return $sqlState === '23000'
            && str_contains($message, 'UNIQUE constraint failed: generation_attempts.job_id');
    }

    private static function applyAttempt(GenerationAttempt $attempt, DesignCallbackData $callback, string $hash): void
    {
        if ($attempt->job_id === null) {
            $attempt->job_id = $callback->jobId;
        }

        $attempt->http_status = $callback->httpStatus;
        $attempt->latency_ms = $callback->latencyMs;
        $attempt->accepted_at = $callback->acceptedAt;
        $attempt->completed_at = $callback->completedAt;
        $attempt->callback_payload_hash = $hash;

        if ($callback->status === 'ok') {
            $attempt->status = 'completed';
            $attempt->error_code = null;
            $attempt->error_message = null;
            $attempt->error_details = null;
            $attempt->retryable = null;
        } else {
            $attempt->status = 'failed';
            $attempt->error_code = $callback->errorCode;
            $attempt->error_message = $callback->message;
            $attempt->error_details = $callback->details;
            $attempt->retryable = $callback->retryable;
        }

        $attempt->save();
    }

    private static function applyGeneration(Generation $generation, DesignCallbackData $callback): void
    {
        if ($callback->status === 'ok') {
            $generation->status = 'completed';
            $generation->sitespec = $callback->sitespec;
            $generation->revision_metadata = $callback->revisionMetadata;
            $generation->usage = $callback->usage;
        } else {
            $generation->status = 'failed';
        }

        $generation->completed_at = $callback->completedAt;
        $generation->save();
    }

    private static function received(bool $applied, ?string $reason, Generation $generation, GenerationAttempt $attempt): DesignCallbackResult
    {
        $body = [
            'received' => true,
            'applied' => $applied,
            'generation_id' => $generation->id,
            'request_id' => $generation->request_id,
            'attempt_id' => $attempt->id,
            'status' => $generation->status,
        ];

        if ($reason !== null) {
            $body = [
                'received' => true,
                'applied' => false,
                'reason' => $reason,
                'generation_id' => $generation->id,
                'request_id' => $generation->request_id,
                'attempt_id' => $attempt->id,
                'status' => $generation->status,
            ];
        }

        return new DesignCallbackResult(200, $body);
    }

    private static function unmatched(): DesignCallbackResult
    {
        return new DesignCallbackResult(200, [
            'received' => true,
            'applied' => false,
            'reason' => 'unmatched_pending_reconciliation',
        ]);
    }

    private static function notFound(): DesignCallbackResult
    {
        return new DesignCallbackResult(404, [
            'received' => false,
            'error_code' => 'not_found',
            'message' => 'Generation attempt not found.',
        ]);
    }

    private static function conflict(string $message): DesignCallbackResult
    {
        return new DesignCallbackResult(409, [
            'received' => false,
            'error_code' => 'identity_conflict',
            'message' => $message,
        ]);
    }
}
