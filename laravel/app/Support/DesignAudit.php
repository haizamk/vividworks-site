<?php

namespace App\Support;

use App\Models\GenerationAuditEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class DesignAudit
{
    /**
     * @param  array<string, mixed>  $context
     */
    public static function record(
        string $event,
        array $context = [],
        ?string $generationId = null,
        ?string $attemptId = null,
        ?string $outboxId = null,
        ?string $inboxId = null,
    ): void {
        GenerationAuditEvent::query()->create([
            'id' => (string) Str::uuid(),
            'event' => $event,
            'generation_id' => $generationId,
            'attempt_id' => $attemptId,
            'outbox_id' => $outboxId,
            'inbox_id' => $inboxId,
            'context' => $context,
            'created_at' => CarbonImmutable::now('UTC'),
        ]);
    }
}
