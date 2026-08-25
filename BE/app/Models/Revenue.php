<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Revenue extends Model
{
    protected $fillable = [
        'instructor_id',
        'course_id',
        'order_id',
        'gross_amount',
        'instructor_amount',
        'platform_fee_amount',
        'commission_rule_id',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'instructor_id' => 'integer',
            'course_id' => 'integer',
            'order_id' => 'integer',
            'commission_rule_id' => 'integer',
            'gross_amount' => 'decimal:2',
            'instructor_amount' => 'decimal:2',
            'platform_fee_amount' => 'decimal:2',
            'earned_at' => 'datetime',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class);
    }

    public function withdrawalRequests(): BelongsToMany
    {
        return $this->belongsToMany(
            WithdrawRequest::class,
            'withdrawal_revenues',
            'revenue_id',
            'withdrawal_id'
        )->withPivot(['allocated_amount', 'created_at']);
    }
}