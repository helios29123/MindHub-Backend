<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_USED_UP = 'used_up';

    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'user_id',
        'course_id',
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_order_amount',
        'usage_limit',
        'used_count',
        'start_at',
        'end_at',
        'status',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_order_amount' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isActiveNow(): bool
    {
        $now = now();

        return $this->status === self::STATUS_ACTIVE
            && ($this->start_at === null || $this->start_at->lte($now))
            && ($this->end_at === null || $this->end_at->gte($now))
            && ($this->usage_limit === null || $this->used_count < $this->usage_limit);
    }
}
