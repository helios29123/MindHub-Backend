<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthSession extends Model
{
    protected $table = 'auth_sessions';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'refresh_token_hash',
        'device_name',
        'ip_address',
        'user_agent',
        'expires_at',
        'revoked_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $hidden = [
        'refresh_token_hash',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isRevoked()
    {
        return $this->revoked_at !== null;
    }

    public function isExpired()
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }
}
