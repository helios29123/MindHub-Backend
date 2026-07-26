<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PayoutBatch extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_LOCKED = 'locked';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    protected $fillable = ['code', 'period_month', 'period_year', 'period_start', 'period_end', 'total_amount', 'total_instructors', 'status', 'created_by', 'paid_by', 'paid_at', 'note'];
    protected $casts = ['total_amount' => 'decimal:2', 'paid_at' => 'datetime'];
    public function items(): HasMany
    {
        return $this->hasMany(PayoutItem::class);
    }
}
