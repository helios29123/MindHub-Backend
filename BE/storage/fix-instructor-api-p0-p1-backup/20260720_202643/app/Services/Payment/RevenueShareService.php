<?php

namespace App\Services\Payment;

use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\Revenue;

final class RevenueShareService
{
    public function ratesForOrder(Order $order): array
    {
        $channel = $order->sale_channel ?: 'marketplace';

        $rule = CommissionRule::query()
            ->where('sale_channel', $channel)
            ->where('is_active', true)
            ->first();

        if ($rule) {
            return [(float) $rule->instructor_rate, (float) $rule->platform_rate, $channel];
        }

        if (in_array($channel, ['instructor_coupon', 'instructor_referral'], true)) {
            return [97.0, 3.0, $channel];
        }

        return [37.0, 63.0, $channel];
    }

    public function createRevenueForPaidOrder(Order $order): Revenue
    {
        if ($order->revenue) {
            return $order->revenue;
        }

        $order->loadMissing('course');
        [$instructorRate, $platformRate, $saleChannel] = $this->ratesForOrder($order);

        $grossAmount = (float) $order->amount;
        $instructorAmount = round($grossAmount * $instructorRate / 100, 2);
        $platformFeeAmount = round($grossAmount - $instructorAmount, 2);

        return Revenue::query()->create([
            'instructor_id' => $order->course->instructor_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'sale_channel' => $saleChannel,
            'instructor_rate_percent' => $instructorRate,
            'platform_rate_percent' => $platformRate,
            'gross_amount' => $grossAmount,
            'instructor_amount' => $instructorAmount,
            'platform_fee_amount' => $platformFeeAmount,
            'status' => 'available',
            'earned_at' => now(),
        ]);
    }
}
