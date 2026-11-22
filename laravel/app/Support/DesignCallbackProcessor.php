<?php

namespace App\Support;

use App\Models\CallbackInbox;
use App\Models\Generation;
use App\Models\GenerationAttempt;
use App\Support\SiteSpec\DesignCallbackSemanticGate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Structural parse, then a committed inbox row, then semantic checks, then the store.
 *
 * A structural failure returns 422 and does not write the inbox.
 * A later failure leaves the inbox row in place.
 */
class DesignCallbackProcessor
{
    public static function handle(string $raw): DesignCallbackResult
    {
        $parsed = DesignCallbackRules::parse($raw);
        if ($parsed['callback'] === null) {
            return self::invalid($parsed['errors']);
        }

        $callback = $parsed['callback'];
        $hash = CallbackPayloadHasher::hash($callback);

        if ($callback->callbackId !== null) {
            $seen = CallbackInbox::query()->where('callback_id', $callback->callbackId)->first();
            if ($seen !== null) {
                DesignAudit::record('callback_duplicate', [
                    'callback_id' => $callback->callbackId,
                    'job_id' => $callback->jobId,
                ]);

                return self::duplicate($callback);
            }
        }

        $inbox = DB::transaction(function () use ($raw, $hash, $callback): CallbackInbox {
            return CallbackInbox::query()->create([
                'id' => (string) Str::uuid(),
                'received_at' => CarbonImmutable::now('UTC'),
                'raw_body' => $raw,
                'payload_hash' => $hash,
                'callback_id' => $callback->callbackId,
                'job_id' => $callback->jobId,
                'request_id' => $callback->requestId,
                'project_id' => $callback->projectId,
                'supplied_attempt_id' => $callback->attemptId,
            ]);
        });

        DesignAudit::record('callback_received', [
            'job_id' => $callback->jobId,
            'request_id' => $callback->requestId,
            'project_id' => $callback->projectId,
            'payload_hash' => $hash,
        ], null, null, null, $inbox->id);

        $errors = DesignCallbackSemanticGate::errors($callback);
        if ($errors !== []) {
            self::finishInbox($inbox, 'invalid_payload');

            return self::invalid($errors);
        }

        $result = DesignCallbackStore::record($callback, $hash);
        $classification = self::classification($result);
        self::finishInbox($inbox, $classification);

        return $result;
    }

    /**
     * @param  array<string, string>  $errors
     */
    private static function invalid(array $errors): DesignCallbackResult
    {
        return new DesignCallbackResult(422, [
            'received' => false,
            'error_code' => 'invalid_payload',
            'message' => 'Callback payload is invalid.',
            'errors' => $errors === [] ? ['body' => 'Callback payload is invalid.'] : $errors,
        ]);
    }

    private static function duplicate(DesignCallbackData $callback): DesignCallbackResult
    {
        $attempt = GenerationAttempt::query()->where('job_id', $callback->jobId)->first();
        $generation = $attempt === null ? null : Generation::query()->find($attempt->generation_id);
        $body = [
            'received' => true,
            'applied' => false,
            'reason' => 'duplicate',
        ];

        if ($generation !== null && $attempt !== null) {
            $body['generation_id'] = $generation->id;
            $body['request_id'] = $generation->request_id;
            $body['attempt_id'] = $attempt->id;
            $body['status'] = $generation->status;
        }

        return new DesignCallbackResult(200, $body);
    }

    private static function finishInbox(CallbackInbox $inbox, string $classification): void
    {
        $inbox->classification = $classification;
        $inbox->processed_at = CarbonImmutable::now('UTC');
        $inbox->save();
    }

    private static function classification(DesignCallbackResult $result): string
    {
        if (($result->body['applied'] ?? false) === true) {
            return 'matched';
        }

        $reason = $result->body['reason'] ?? null;
        if (is_string($reason) && $reason !== '') {
            return $reason;
        }

        $code = $result->body['error_code'] ?? null;

        return is_string($code) && $code !== '' ? $code : 'received';
    }
}
