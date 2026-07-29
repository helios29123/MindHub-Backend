<?php

namespace App\Services\Payment;

use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\Revenue;

final class RevenueShareService
{
    public function calculateForOrder(Order $order): array
    {
        $channel = 'marketplace';
        if (isset($order->sale_channel) && $order->sale_channel) {
            $channel = $order->sale_channel;
        } elseif ($order->coupon_id !== null) {
            $channel = 'instructor_coupon';
        }

        $rule = CommissionRule::query()
            ->where('sale_channel', $channel)
            ->where('is_active', true)
            ->first();

        if ($rule) {
            $instructorRate = (float) $rule->instructor_rate;
            $platformRate = (float) $rule->platform_rate;
            $ruleId = $rule->id;
            $ruleCode = $channel;
        } else {
            $instructorRate = 70.0;
            $platformRate = 30.0;
            $ruleId = null;
            $ruleCode = 'default';
        }

        $grossAmount = (float) $order->amount;
        $instructorAmount = round($grossAmount * $instructorRate / 100, 2);
        $platformFeeAmount = round($grossAmount - $instructorAmount, 2);

        return [
            'gross_amount' => $grossAmount,
            'instructor_percent' => $instructorRate,
            'platform_percent' => $platformRate,
            'instructor_amount' => $instructorAmount,
            'platform_fee_amount' => $platformFeeAmount,
            'rule_id' => $ruleId,
            'rule_code' => $ruleCode,
        ];
    }

    public function createRevenueForPaidOrder(Order $order): Revenue
    {
        $existing = Revenue::where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        $order->loadMissing('course');
        $calc = $this->calculateForOrder($order);

        return Revenue::query()->create([
            'instructor_id' => $order->course->instructor_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'gross_amount' => $calc['gross_amount'],
            'instructor_amount' => $calc['instructor_amount'],
            'platform_fee_amount' => $calc['platform_fee_amount'],
            'status' => 'available',
            'earned_at' => $order->paid_at ?: now(),
        ]);
    }
}
