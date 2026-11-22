<?php

namespace App\Models;

use App\Casts\JsonValueCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenerationOutbox extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'generation_outbox';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'attempt_id',
        'state',
        'delivery_classification',
        'body',
        'http_status',
        'claimed_at',
        'dead_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'body' => JsonValueCast::class,
            'http_status' => 'integer',
            'claimed_at' => 'datetime',
            'dead_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(GenerationAttempt::class, 'attempt_id');
    }
}
