<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
final class Session extends Model
{
    protected $table = 'sessions';
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
    protected $hidden = [
        'refresh_token_hash',
    ];
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function isActive(): bool
    {
        return $this->revoked_at === null
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }
}