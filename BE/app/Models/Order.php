<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_EXPIRED = 'expired';

    protected $fillable = [
        'order_code',
        'user_id',
        'course_id',
        'coupon_id',
        'commission_rule_id',
        'status',
        'payment_status',
        'price_snapshot',
        'discount_amount',
        'amount',
        'payment_method',
        'provider_transaction_id',
        'paid_at',
        'expires_at',
        'cancelled_reason',
        'failed_reason',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'course_id' => 'integer',
            'coupon_id' => 'integer',
            'commission_rule_id' => 'integer',
            'price_snapshot' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

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

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class);
    }

    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class);
    }

    public function revenue(): HasOne
    {
        return $this->hasOne(Revenue::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(CourseReview::class);
    }
}