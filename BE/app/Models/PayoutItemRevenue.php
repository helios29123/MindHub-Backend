<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PayoutItemRevenue extends Model
{
    protected $fillable = ['payout_item_id', 'revenue_id', 'amount'];
    protected $casts = ['amount' => 'decimal:2'];
}
