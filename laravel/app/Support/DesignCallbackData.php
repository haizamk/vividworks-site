<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use stdClass;

/**
 * Validated design callback. The envelope schema_version is 1.1.
 */
class DesignCallbackData
{
    public function __construct(
        public string $status,
        public ?string $generationId,
        public string $projectId,
        public string $requestId,
        public ?string $attemptId,
        public string $jobId,
        public ?int $httpStatus,
        public CarbonImmutable $acceptedAt,
        public CarbonImmutable $completedAt,
        public int $latencyMs,
        public ?stdClass $sitespec,
        public ?stdClass $revisionMetadata,
        public ?stdClass $usage,
        public ?string $errorCode,
        public ?string $message,
        public ?stdClass $details,
        public ?bool $retryable,
        public ?string $callbackId = null,
    ) {}

    /**
     * Fields that identify one callback result. The authentication token is never included.
     *
     * @return array<string, mixed>
     */
    public function canonicalFields(): array
    {
        return [
            'accepted_at' => $this->acceptedAt->utc()->format('Y-m-d\TH:i:s.u\Z'),
            'attempt_id' => $this->attemptId,
            'completed_at' => $this->completedAt->utc()->format('Y-m-d\TH:i:s.u\Z'),
            'details' => $this->details,
            'error_code' => $this->errorCode,
            'generation_id' => $this->generationId,
            'http_status' => $this->httpStatus,
            'job_id' => $this->jobId,
            'latency_ms' => $this->latencyMs,
            'message' => $this->message,
            'project_id' => $this->projectId,
            'request_id' => $this->requestId,
            'retryable' => $this->retryable,
            'revision_metadata' => $this->revisionMetadata,
            'schema_version' => '1.1',
            'sitespec' => $this->sitespec,
            'stage' => 'design_agent',
            'status' => $this->status,
            'usage' => $this->usage,
            'callback_id' => $this->callbackId,
        ];
    }
}
