<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallbackInbox extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'callback_inbox';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'received_at',
        'raw_body',
        'payload_hash',
        'callback_id',
        'job_id',
        'request_id',
        'project_id',
        'supplied_attempt_id',
        'classification',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
