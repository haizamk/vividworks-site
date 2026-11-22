<?php

namespace App\Models;

use App\Casts\JsonValueCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Generation extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'generations';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'project_id',
        'request_id',
        'status',
        'current_attempt_id',
        'sitespec',
        'revision_metadata',
        'usage',
        'accepted_at',
        'completed_at',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sitespec' => JsonValueCast::class,
            'revision_metadata' => JsonValueCast::class,
            'usage' => JsonValueCast::class,
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(GenerationAttempt::class, 'generation_id');
    }

    public function currentAttempt(): BelongsTo
    {
        return $this->belongsTo(GenerationAttempt::class, 'current_attempt_id');
    }
}
