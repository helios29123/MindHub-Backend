<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class PayoutItem extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ON_HOLD = 'on_hold';
    protected $fillable = ['payout_batch_id', 'instructor_id', 'payout_account_id', 'gross_amount', 'instructor_amount', 'platform_fee_amount', 'paid_amount', 'status', 'transaction_code', 'paid_at', 'note'];
    protected $casts = ['gross_amount' => 'decimal:2', 'instructor_amount' => 'decimal:2', 'platform_fee_amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'paid_at' => 'datetime'];
    public function batch(): BelongsTo
    {
        return $this->belongsTo(PayoutBatch::class, 'payout_batch_id');
    }
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(PayoutAccount::class);
    }
    public function revenues(): BelongsToMany
    {
        return $this->belongsToMany(Revenue::class, 'payout_item_revenues')->withPivot('amount')->withTimestamps();
    }
}
