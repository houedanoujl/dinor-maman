<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    public const TYPE_CREDENTIALS = 'credentials';
    public const TYPE_APPROVAL    = 'approval';
    public const TYPE_REJECTION   = 'rejection';

    public const STATUS_SENT      = 'sent';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_SKIPPED   = 'skipped';

    protected $fillable = [
        'phone',
        'type',
        'provider',
        'status',
        'message',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
