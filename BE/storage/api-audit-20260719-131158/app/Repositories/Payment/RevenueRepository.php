<?php

namespace App\Repositories\Payment;

use App\Models\Revenue;

class RevenueRepository
{
    public function findByOrderId(int $orderId)
    {
        return Revenue::where('order_id', $orderId)->first();
    }
    public function create(array $revenueData)
    {
        return Revenue::create($revenueData);
    }
    
}
