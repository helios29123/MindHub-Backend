<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Revenue extends Model
{
    use HasFactory;

    protected $table = 'revenues';
    const UPDATED_AT = null;

    public const STATUS_PENDING = 'pending';
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_INCLUDED_IN_PAYOUT = 'included_in_payout';
    public const STATUS_PAID = 'paid';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'order_id',
        'course_id',
        'instructor_id',
        'payout_id',
        'commission_rule_id',
        'commission_rule_code',
        'gross_amount',
        'instructor_amount',
        'platform_fee_amount',
        'instructor_percent',
        'platform_percent',
        'sale_source',
        'status',
        'earned_at',
        'available_at',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'course_id' => 'integer',
        'instructor_id' => 'integer',
        'payout_id' => 'integer',
        'commission_rule_id' => 'integer',
        'gross_amount' => 'decimal:2',
        'instructor_amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'instructor_percent' => 'decimal:2',
        'platform_percent' => 'decimal:2',
        'earned_at' => 'datetime',
        'available_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'commission_rule_id');
    }

    public function withdrawRequest(): BelongsTo
    {
        return $this->belongsTo(WithdrawRequest::class, 'payout_id');
    }

    public function withdrawalAllocations(): BelongsToMany
    {
        return $this->belongsToMany(
            WithdrawRequest::class,
            'withdrawal_revenues',
            'revenue_id',
            'withdrawal_id'
        )->withPivot('allocated_amount')->withTimestamps();
    }
}
