<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WithdrawRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_MANUAL_REQUIRED = 'manual_required';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    protected $table = 'withdraw_requests';

    protected $fillable = [
        'user_id',
        'payout_account_id',
        'amount',
        'status',
        'requested_at',
        'approved_at',
        'paid_at',
        'processed_at',
        'provider_payout_id',
        'failure_reason',
        'rejected_reason',
        'admin_note',
        'account_number_snapshot',
        'account_name_snapshot',
        'available_balance_before',
        'available_balance_after',
        'bank_name_snapshot',
        'payout_provider',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'payout_account_id' => 'integer',
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'processed_at' => 'datetime',
            'available_balance_before' => 'decimal:2',
            'available_balance_after' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(PayoutAccount::class);
    }

    public function revenues(): BelongsToMany
    {
        return $this->belongsToMany(
            Revenue::class,
            'withdrawal_revenues',
            'withdrawal_id',
            'revenue_id'
        )->withPivot(['allocated_amount', 'created_at']);
    }
}