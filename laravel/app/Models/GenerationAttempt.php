<?php

namespace App\Models;

use App\Casts\JsonValueCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenerationAttempt extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'generation_attempts';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * Terminal callback results are never rewritten.
     *
     * @var list<string>
     */
    public const TERMINAL_STATUSES = ['completed', 'failed', 'expired', 'timed_out', 'superseded'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'generation_id',
        'job_id',
        'attempt_number',
        'status',
        'accepted_at',
        'submitted_at',
        'completed_at',
        'latency_ms',
        'http_status',
        'error_code',
        'error_message',
        'error_details',
        'retryable',
        'callback_payload_hash',
        'invoke_correlation_key',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'latency_ms' => 'integer',
            'http_status' => 'integer',
            'retryable' => 'boolean',
            'error_details' => JsonValueCast::class,
            'accepted_at' => 'datetime',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class, 'generation_id');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }
}
