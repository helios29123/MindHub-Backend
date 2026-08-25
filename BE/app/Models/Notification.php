<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public const CHANNEL_WEB = 'web';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_BOTH = 'both';

    public const EMAIL_PENDING = 'pending';
    public const EMAIL_SENT = 'sent';
    public const EMAIL_FAILED = 'failed';
    public const EMAIL_SKIPPED = 'skipped';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'channel',
        'read_at',
        'email_status',
        'email_sent_at',
        'email_error',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'data' => 'array',
            'read_at' => 'datetime',
            'email_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}