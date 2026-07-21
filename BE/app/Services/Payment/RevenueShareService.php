<?php

namespace App\Services\Payment;

use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\Revenue;

final class RevenueShareService
{
    public function calculateForOrder(Order $order): array
    {
        $resolvedSource = $this->resolveSaleSource($order);
        $ruleData = $this->resolveCommissionRule($resolvedSource);

        $instructorRate = $ruleData['instructor_percent'];
        $platformRate = $ruleData['platform_percent'];
        $ruleId = $ruleData['rule_id'];
        $ruleCode = $ruleData['rule_code'];

        $grossAmount = (float) $order->amount;

        if ($grossAmount <= 0) {
            $instructorAmount = 0.0;
            $platformFeeAmount = 0.0;
        } else {
            $instructorAmount = round($grossAmount * $instructorRate / 100, 2);
            $platformFeeAmount = round($grossAmount - $instructorAmount, 2);
        }

        return [
            'sale_source' => $resolvedSource,
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

        $order->update([
            'sale_source' => $calc['sale_source'],
            'commission_rule_id' => $calc['rule_id']
        ]);

        return Revenue::query()->create([
            'instructor_id' => $order->course->instructor_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'gross_amount' => $calc['gross_amount'],
            'instructor_amount' => $calc['instructor_amount'],
            'platform_fee_amount' => $calc['platform_fee_amount'],
            'status' => 'available',
            'earned_at' => $order->paid_at ?: now(),
            'sale_source' => $calc['sale_source'],
            'commission_rule_id' => $calc['rule_id'],
            'commission_rule_code' => $calc['rule_code'],
            'instructor_percent' => $calc['instructor_percent'],
            'platform_percent' => $calc['platform_percent'],
        ]);
    }

    public function resolveSaleSource(Order $order): string
    {
        $order->loadMissing('course');
        $course = $order->course;

        if ($order->coupon_id !== null) {
            $order->loadMissing('coupon');
            $coupon = $order->coupon;
            if ($coupon && $course) {
                if ($this->isInstructorOwnedCoupon($order, $coupon, $course)) {
                    return 'instructor_coupon';
                }
                if ($this->isAdminOrPlatformCoupon($coupon)) {
                    return $order->sale_source === 'platform_ads' ? 'platform_ads' : 'admin_campaign';
                }
            }
        }

        $source = $order->sale_source ?: $order->sale_channel;

        $validSources = [
            'marketplace_default',
            'platform_ads',
            'admin_campaign',
            'instructor_coupon',
            'instructor_referral',
        ];

        if ($source === 'marketplace') {
            return 'marketplace_default';
        }

        if (!$source || !in_array($source, $validSources, true)) {
            return 'marketplace_default';
        }

        return $source;
    }

    public function resolveCommissionRule(string $saleSource): array
    {
        $rule = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('commission_rules')) {
            try {
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing('commission_rules');
                $rule = CommissionRule::query()
                    ->where(function ($query) use ($saleSource, $columns) {
                        $first = true;
                        if (in_array('sale_channel', $columns, true)) {
                            $query->where('sale_channel', $saleSource);
                            $first = false;
                        }
                        if (in_array('code', $columns, true)) {
                            if ($first) {
                                $query->where('code', $saleSource);
                                $first = false;
                            } else {
                                $query->orWhere('code', $saleSource);
                            }
                        }
                        if (in_array('type', $columns, true)) {
                            if ($first) {
                                $query->where('type', $saleSource);
                            } else {
                                $query->orWhere('type', $saleSource);
                            }
                        }
                    })
                    ->where('is_active', true)
                    ->first();
            } catch (\Throwable $e) {
                $rule = null;
            }
        }

        if (!$rule) {
            try {
                $rule = CommissionRule::query()
                    ->where('sale_channel', 'marketplace_default')
                    ->where('is_active', true)
                    ->first();
            } catch (\Throwable $e) {
                $rule = null;
            }
        }

        if ($rule) {
            return [
                'instructor_percent' => (float) $rule->instructor_rate,
                'platform_percent' => (float) $rule->platform_rate,
                'rule_id' => $rule->id,
                'rule_code' => $rule->sale_channel ?: $rule->code ?: $saleSource,
            ];
        }

        return [
            'instructor_percent' => 70.0,
            'platform_percent' => 30.0,
            'rule_id' => null,
            'rule_code' => 'marketplace_default',
        ];
    }

    private function isInstructorOwnedCoupon(Order $order, \App\Models\Coupon $coupon, \App\Models\Course $course): bool
    {
        if (isset($coupon->instructor_id) && $coupon->instructor_id == $course->instructor_id) {
            return true;
        }
        if (isset($coupon->user_id) && $coupon->user_id == $course->instructor_id) {
            return true;
        }
        if (isset($coupon->owner_type) && strtolower($coupon->owner_type) === 'instructor') {
            return true;
        }
        if (isset($coupon->type) && strtolower($coupon->type) === 'instructor') {
            return true;
        }
        if (isset($coupon->source) && strtolower($coupon->source) === 'instructor') {
            return true;
        }

        if (isset($coupon->course_id) && $coupon->course_id == $order->course_id) {
            if (isset($coupon->owner_type) && in_array(strtolower($coupon->owner_type), ['admin', 'system', 'platform'])) {
                return false;
            }
            if (isset($coupon->type) && in_array(strtolower($coupon->type), ['admin', 'system', 'platform'])) {
                return false;
            }
            if (isset($coupon->source) && in_array(strtolower($coupon->source), ['admin', 'system', 'platform'])) {
                return false;
            }
            if ($coupon->user_id === null) {
                return false;
            }
            $user = \App\Models\User::find($coupon->user_id);
            if ($user && $user->role === 'instructor') {
                return true;
            }
        }

        return false;
    }

    private function isAdminOrPlatformCoupon(\App\Models\Coupon $coupon): bool
    {
        if (isset($coupon->owner_type) && in_array(strtolower($coupon->owner_type), ['admin', 'system', 'platform'])) {
            return true;
        }
        if (isset($coupon->type) && in_array(strtolower($coupon->type), ['admin', 'system', 'platform'])) {
            return true;
        }
        if (isset($coupon->source) && in_array(strtolower($coupon->source), ['admin', 'system', 'platform'])) {
            return true;
        }
        if ($coupon->user_id === null) {
            return true;
        }
        $user = \App\Models\User::find($coupon->user_id);
        if ($user && $user->role === 'admin') {
            return true;
        }
        return false;
    }
}
