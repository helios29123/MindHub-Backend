<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const TYPE_COURSE_PURCHASE = 'course_purchase';
    public const TYPE_INSTRUCTOR_CREDIT = 'instructor_credit';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    /*
    |--------------------------------------------------------------------------
    | Payment status
    |--------------------------------------------------------------------------
    | DB hiện tại đang nhận unpaid/paid/failed.
    | Không lưu payment_status = pending nếu constraint DB không cho phép.
    | processing chỉ dùng để so sánh logic, không update DB sang processing.
    */
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PENDING = 'unpaid';
    public const PAYMENT_PROCESSING = 'processing';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'course_id',
        'coupon_id',
        'order_code',
        'order_type',
        'credit_package_id',
        'package_snapshot_name',
        'package_snapshot_credits',
        'price',
        'price_snapshot',
        'amount',
        'discount_amount',
        'final_amount',
        'status',
        'payment_status',
        'payment_method',
        'provider_transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'coupon_id' => 'integer',
        'credit_package_id' => 'integer',
        'package_snapshot_credits' => 'integer',
        'price' => 'decimal:2',
        'price_snapshot' => 'decimal:2',
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

public function course()
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    public function coupon()
    {
        $foreignKey = \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'coupon_id')
            ? 'coupon_id'
            : (
                \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'discount_code_id')
                    ? 'discount_code_id'
                    : (
                        \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'coupon_code_id')
                            ? 'coupon_code_id'
                            : 'coupon_id'
                    )
            );

        $model = class_exists(\App\Models\Coupon::class)
            ? \App\Models\Coupon::class
            : (
                class_exists(\App\Models\DiscountCode::class)
                    ? \App\Models\DiscountCode::class
                    : \App\Models\Coupon::class
            );

        return $this->belongsTo($model, $foreignKey);
    }
}
