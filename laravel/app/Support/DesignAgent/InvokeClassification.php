<?php

namespace App\Support\DesignAgent;

/**
 * Technical delivery class for one mock invoke.
 * These names are delivery results. They are not a retry policy.
 */
class InvokeClassification
{
    public const ACCEPTED_202 = 'accepted_202';

    public const REJECTED_400 = 'rejected_400';

    public const REJECTED_OTHER = 'rejected_other';

    public const CONFIRMED_NOT_SENT = 'confirmed_not_sent';

    public const UNKNOWN = 'unknown';

    /**
     * @param  array<string, mixed>|null  $details
     */
    public function __construct(
        public string $name,
        public ?int $httpStatus = null,
        public ?string $jobId = null,
        public ?string $errorCode = null,
        public ?string $message = null,
        public ?array $details = null,
        public bool $positiveProofNotSent = false,
    ) {}

    public static function unknown(): self
    {
        return new self(self::UNKNOWN);
    }

    public static function accepted202(string $jobId): self
    {
        return new self(self::ACCEPTED_202, 202, $jobId);
    }

    /**
     * @param  array<string, mixed>|null  $details
     */
    public static function rejected400(?string $message = null, ?array $details = null): self
    {
        return new self(self::REJECTED_400, 400, null, 'invalid_input', $message, $details);
    }

    public static function rejectedOther(int $httpStatus): self
    {
        return new self(self::REJECTED_OTHER, $httpStatus);
    }

    public static function confirmedNotSent(): self
    {
        return new self(self::CONFIRMED_NOT_SENT, null, null, null, null, null, true);
    }
}
