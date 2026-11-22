<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerationReopen extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $table = 'generation_reopens';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'generation_id',
        'previous_attempt_id',
        'new_attempt_id',
        'actor',
        'reason',
        'duplicate_risk_acknowledged',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duplicate_risk_acknowledged' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
