<?php

namespace App\Repositories\Payment;

use App\Models\Coupon;

class CouponRepository
{
    public function findByCode(string $couponCode): ?Coupon
    {
        return Coupon::where('code', strtoupper(trim($couponCode)))->first();
    }

    public function incrementUsedCount(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }
}