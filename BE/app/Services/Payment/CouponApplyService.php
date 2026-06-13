<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Coupon;
use App\Models\Order;
use App\Repositories\Payment\OrderRepository;
use App\Repositories\Payment\CouponRepository;
use Illuminate\Support\Facades\DB;

class CouponApplyService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly CouponRepository $couponRepository
    ) {
    }

    public function applyCoupon(array $couponData, int $userId): Order
    {
        return DB::transaction(function () use ($couponData, $userId) {
            $order = $this->orderRepository->findUserOrderForUpdate(
                $couponData['order_id'],
                $userId
            );

            if (!$order) {
                throw new BusinessException('Không tìm thấy đơn hàng.', 404);
            }

            if (
                $order->status !== Order::STATUS_PENDING ||
                $order->payment_status !== Order::PAYMENT_UNPAID
            ) {
                throw new BusinessException('Chỉ có thể áp coupon cho đơn hàng đang chờ thanh toán.', 400);
            }

            $coupon = $this->couponRepository->findByCode($couponData['coupon_code']);

            if (!$coupon || !$coupon->isActiveNow()) {
                throw new BusinessException('Mã giảm giá không hợp lệ.', 400);
            }

            if (
                $coupon->course_id !== null &&
                (int) $coupon->course_id !== (int) $order->course_id
            ) {
                throw new BusinessException('Mã giảm giá không áp dụng cho khóa học này.', 400);
            }

            $discountAmount = $this->calculateDiscountAmount($coupon, (float) $order->price_snapshot);
            $finalAmount = max(0, (float) $order->price_snapshot - $discountAmount);

            $order->update([
                'coupon_id' => $coupon->id,
                'amount' => $finalAmount,
            ]);

            return $order->fresh(['course', 'coupon']);
        });
    }

    private function calculateDiscountAmount(Coupon $coupon, float $price): float
    {
        if ($coupon->discount_type === Coupon::TYPE_PERCENT) {
            $discountAmount = $price * ((float) $coupon->discount_value / 100);

            if ($coupon->max_order_amount !== null) {
                $discountAmount = min($discountAmount, (float) $coupon->max_order_amount);
            }

            return $discountAmount;
        }

        return min((float) $coupon->discount_value, $price);
    }
}
