<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PROCESSING = 'processing';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';

    protected $fillable = [
        'coupon_id',
        'course_id',
        'user_id',
        'order_code',
        'status',
        'price_snapshot',
        'payment_method',
        'provider_transaction_id',
        'amount',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class);
    }

    public function revenue(): HasOne
    {
        return $this->hasOne(Revenue::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID
            && $this->payment_status === self::PAYMENT_PAID;
    }
}
