<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WithdrawRequest extends Model
{
    use HasFactory;

    protected $table = 'withdraw_requests';

    public const TYPE_AUTOMATIC_PAYOUT = 'automatic_payout';
    public const TYPE_EARLY_WITHDRAWAL = 'early_withdrawal';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_MANUAL_REQUIRED = 'manual_required';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'payout_account_id',
        'amount',
        'status',
        'type',
        'period_start',
        'period_end',
        'expected_payment_at',
        'requested_at',
        'paid_at',
        'processed_at',
        'bank_name',
        'account_number_snapshot',
        'account_name_snapshot',
        'payout_method',
        'payout_provider',
        'provider_payout_id',
        'available_balance_before',
        'available_balance_after',
        'blocked_reason',
        'failure_reason',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'available_balance_before' => 'decimal:2',
        'available_balance_after' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'expected_payment_at' => 'datetime',
        'requested_at' => 'datetime',
        'paid_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(PayoutAccount::class, 'payout_account_id');
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class, 'payout_id');
    }

    public function allocatedRevenues(): BelongsToMany
    {
        return $this->belongsToMany(Revenue::class, 'withdrawal_revenues', 'withdrawal_id', 'revenue_id')
            ->withPivot('allocated_amount')
            ->withTimestamps();
    }
}