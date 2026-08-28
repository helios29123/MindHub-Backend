<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;

class CouponApplyService
{
    public function applyCoupon(array $couponData, int $userId): never
    {
        throw new BusinessException(
            'Coupon hiện tại được tự động áp dụng theo khóa học. Learner không nhập mã coupon.',
            410
        );
    }

    public function apply(array $data, int $userId): never
    {
        $this->applyCoupon($data, $userId);
    }
}
