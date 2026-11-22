<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenerationContext extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'generation_id';

    protected $table = 'generation_contexts';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'generation_id',
        'project_id',
        'request_id',
        'page_count',
        'active_templates',
        'media_allowlist',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_count' => 'integer',
            'active_templates' => 'array',
            'media_allowlist' => 'array',
        ];
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class, 'generation_id');
    }
}
