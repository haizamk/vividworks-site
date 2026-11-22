<?php

namespace App\Support;

use App\Support\SiteSpec\SiteSpecStructuralValidator;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use JsonException;
use stdClass;
use Throwable;

/**
 * Structural validation for the design callback.
 *
 * The envelope root schema_version is 1.1. A success sitespec is a stdClass
 * checked by SiteSpecStructuralValidator before this method returns a callback.
 * revision_metadata.prompt_version is v1.1.0 and is not a schema version.
 */
class DesignCallbackRules
{
    private const UUID = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    private const TIMESTAMP = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{1,6})?(Z|[+-]\d{2}:\d{2})$/';

    /**
     * @var list<string>
     */
    private const SHARED_FIELDS = [
        'schema_version',
        'status',
        'generation_id',
        'project_id',
        'request_id',
        'attempt_id',
        'job_id',
        'stage',
        'http_status',
        'accepted_at',
        'completed_at',
        'latency_ms',
    ];

    /**
     * @var list<string>
     */
    private const SUCCESS_FIELDS = ['sitespec', 'revision_metadata', 'usage'];

    /**
     * @var list<string>
     */
    private const ERROR_FIELDS = ['error'];

    /**
     * @var list<string>
     */
    private const OPTIONAL_FIELDS = ['callback_id'];

    /**
     * @return array{errors: array<string, string>, callback: DesignCallbackData|null}
     */
    public static function parse(string $raw): array
    {
        try {
            $decoded = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return self::failed(['body' => 'Body must be a JSON object.']);
        }

        if (! $decoded instanceof stdClass) {
            return self::failed(['body' => 'Body must be a JSON object.']);
        }

        $errors = [];
        $status = $decoded->status ?? null;
        if ($status !== 'ok' && $status !== 'error') {
            $errors['status'] = 'status must be ok or error.';
        }

        if (($decoded->schema_version ?? null) !== '1.1') {
            $errors['schema_version'] = 'schema_version must be 1.1.';
        }

        if (($decoded->stage ?? null) !== 'design_agent') {
            $errors['stage'] = 'stage must be design_agent.';
        }

        $generationId = self::optionalUuid($decoded, 'generation_id', $errors);
        $attemptId = self::optionalUuid($decoded, 'attempt_id', $errors);
        $projectId = self::requiredString($decoded->project_id ?? null);
        $requestId = self::requiredString($decoded->request_id ?? null);
        $jobId = self::requiredString($decoded->job_id ?? null);
        $callbackId = self::optionalString($decoded, 'callback_id', $errors);

        if ($projectId === null) {
            $errors['project_id'] = 'project_id is required.';
        }
        if ($requestId === null) {
            $errors['request_id'] = 'request_id is required.';
        }
        if ($jobId === null) {
            $errors['job_id'] = 'job_id is required.';
        }

        $acceptedAt = self::optionalTimestamp($decoded, 'accepted_at', $errors);
        $completedAt = self::optionalTimestamp($decoded, 'completed_at', $errors);
        $latencyMs = self::optionalLatency($decoded, $errors);

        $httpStatus = self::httpStatus($decoded, $status === 'ok', $errors);

        $sitespec = null;
        $revisionMetadata = null;
        $usage = null;
        $errorCode = null;
        $message = null;
        $details = null;
        $retryable = null;

        if ($status === 'ok' || $status === 'error') {
            $allowed = array_merge(self::SHARED_FIELDS, self::OPTIONAL_FIELDS, $status === 'ok' ? self::SUCCESS_FIELDS : self::ERROR_FIELDS);
            foreach (array_keys(get_object_vars($decoded)) as $key) {
                if (! in_array($key, $allowed, true)) {
                    $errors['body'] = 'Payload contains a field that is not in contract 1.1.';
                    break;
                }
            }
        }

        if ($status === 'ok') {
            $sitespec = self::objectField($decoded->sitespec ?? null);
            $revisionMetadata = self::objectField($decoded->revision_metadata ?? null);
            if ($sitespec === null) {
                $errors['sitespec'] = 'sitespec must be an object.';
            } else {
                foreach (SiteSpecStructuralValidator::fromCanonicalFile()->validate($sitespec)->violations as $violation) {
                    $suffix = $violation->path === '/' ? '' : $violation->path;
                    $errors['sitespec'.$suffix] = $violation->message;
                }
            }
            if ($revisionMetadata === null) {
                $errors['revision_metadata'] = 'revision_metadata must be an object.';
            } elseif (($revisionMetadata->prompt_version ?? null) !== 'v1.1.0') {
                $errors['revision_metadata'] = 'revision_metadata.prompt_version must be v1.1.0.';
            }
            if (property_exists($decoded, 'usage') && $decoded->usage !== null) {
                $usage = self::objectField($decoded->usage);
                if ($usage === null) {
                    $errors['usage'] = 'usage must be an object when present.';
                }
            }
        }

        if ($status === 'error') {
            $error = self::objectField($decoded->error ?? null);
            if ($error === null) {
                $errors['error'] = 'error must be an object.';
            } else {
                $errorCode = self::requiredString($error->error_code ?? null);
                $message = self::requiredString($error->message ?? null);
                if ($errorCode === null) {
                    $errors['error.error_code'] = 'error.error_code is required.';
                }
                if ($message === null) {
                    $errors['error.message'] = 'error.message is required.';
                }
                if (property_exists($error, 'details') && $error->details !== null) {
                    $details = self::objectField($error->details);
                    if ($details === null) {
                        $errors['error.details'] = 'error.details must be an object when present.';
                    }
                }
                if (property_exists($error, 'retryable') && $error->retryable !== null) {
                    if (! is_bool($error->retryable)) {
                        $errors['error.retryable'] = 'error.retryable must be a boolean when present.';
                    } else {
                        $retryable = $error->retryable;
                    }
                }
            }
        }

        if ($errors !== [] || $projectId === null || $requestId === null || $jobId === null || $acceptedAt === null || $completedAt === null || $latencyMs === null) {
            return self::failed($errors === [] ? ['body' => 'Callback payload is invalid.'] : $errors);
        }

        return [
            'errors' => [],
            'callback' => new DesignCallbackData(
                status: $status,
                generationId: $generationId,
                projectId: $projectId,
                requestId: $requestId,
                attemptId: $attemptId,
                jobId: $jobId,
                httpStatus: $httpStatus,
                acceptedAt: $acceptedAt,
                completedAt: $completedAt,
                latencyMs: $latencyMs,
                sitespec: $sitespec,
                revisionMetadata: $revisionMetadata,
                usage: $usage,
                errorCode: $errorCode,
                message: $message,
                details: $details,
                retryable: $retryable,
                callbackId: $callbackId,
            ),
        ];
    }

    /**
     * @param  array<string, string>  $errors
     * @return array{errors: array<string, string>, callback: null}
     */
    private static function failed(array $errors): array
    {
        return ['errors' => $errors, 'callback' => null];
    }

    /**
     * @param  array<string, string>  $errors
     */
    private static function httpStatus(stdClass $decoded, bool $required, array &$errors): ?int
    {
        $present = property_exists($decoded, 'http_status');
        $value = $present ? $decoded->http_status : null;

        if (! $present || $value === null) {
            if ($required) {
                $errors['http_status'] = 'http_status must be an integer from 100 to 599.';
            }

            return null;
        }

        if (! is_int($value) || $value < 100 || $value > 599) {
            $errors['http_status'] = 'http_status must be an integer from 100 to 599.';

            return null;
        }

        return $value;
    }

    /**
     * @param  array<string, string>  $errors
     */
    private static function optionalUuid(stdClass $decoded, string $field, array &$errors): ?string
    {
        if (! property_exists($decoded, $field)) {
            return null;
        }

        $value = self::uuid($decoded->{$field});
        if ($value === null) {
            $errors[$field] = $field.' must be a UUID.';
        }

        return $value;
    }

    /**
     * @param  array<string, string>  $errors
     */
    private static function optionalString(stdClass $decoded, string $field, array &$errors): ?string
    {
        if (! property_exists($decoded, $field) || $decoded->{$field} === null) {
            return null;
        }

        $value = self::requiredString($decoded->{$field});
        if ($value === null) {
            $errors[$field] = $field.' must be a string.';
        }

        return $value;
    }

    /**
     * @param  array<string, string>  $errors
     */
    private static function optionalTimestamp(stdClass $decoded, string $field, array &$errors): ?CarbonImmutable
    {
        if (! property_exists($decoded, $field)) {
            return CarbonImmutable::now('UTC');
        }

        $value = self::timestamp($decoded->{$field});
        if ($value === null) {
            $errors[$field] = $field.' must be an ISO-8601 timestamp.';
        }

        return $value;
    }

    /**
     * @param  array<string, string>  $errors
     */
    private static function optionalLatency(stdClass $decoded, array &$errors): ?int
    {
        if (! property_exists($decoded, 'latency_ms')) {
            return 0;
        }

        if (! is_int($decoded->latency_ms) || $decoded->latency_ms < 0) {
            $errors['latency_ms'] = 'latency_ms must be a non-negative integer.';

            return null;
        }

        return $decoded->latency_ms;
    }

    private static function uuid(mixed $value): ?string
    {
        if (! is_string($value) || preg_match(self::UUID, trim($value)) !== 1) {
            return null;
        }

        return strtolower(trim($value));
    }

    private static function requiredString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private static function objectField(mixed $value): ?stdClass
    {
        return $value instanceof stdClass ? $value : null;
    }

    private static function timestamp(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || preg_match(self::TIMESTAMP, $value) !== 1) {
            return null;
        }

        try {
            $parsed = CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }

        if (! $parsed instanceof DateTimeInterface) {
            return null;
        }

        return $parsed->utc();
    }
}
