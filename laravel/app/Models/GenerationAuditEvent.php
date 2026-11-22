<?php

namespace App\Models;

use App\Casts\JsonValueCast;
use Illuminate\Database\Eloquent\Model;

class GenerationAuditEvent extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $table = 'generation_audit_events';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'event',
        'generation_id',
        'attempt_id',
        'outbox_id',
        'inbox_id',
        'context',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context' => JsonValueCast::class,
            'created_at' => 'datetime',
        ];
    }
}
