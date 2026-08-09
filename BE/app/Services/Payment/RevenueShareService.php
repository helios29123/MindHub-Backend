<?php

namespace App\Services\Payment;

use App\Exceptions\CommissionRuleNotFoundException;
use App\Exceptions\CourseInstructorMissingException;
use App\Exceptions\InvalidCommissionRuleException;
use App\Exceptions\InvalidOrderAmountException;
use App\Exceptions\OrderNotPaidException;
use App\Models\CommissionRule;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\Revenue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class RevenueShareService
{
    /**
     * Preview calculation for an order (without DB mutations).
     */
    public function calculateForOrder(Order $order): array
    {
        if ($order->amount < 0 || $order->amount === null) {
            throw new InvalidOrderAmountException('Order amount is invalid or negative for revenue calculation.');
        }

        $resolvedSource = $this->resolveSaleSource($order);
        $ruleData = $this->resolveCommissionRule($resolvedSource);

        $instructorRate = (float) $ruleData['instructor_percent'];
        $platformRate = (float) $ruleData['platform_percent'];
        $ruleId = $ruleData['rule_id'];
        $ruleCode = $ruleData['rule_code'];

        $this->validateRates($instructorRate, $platformRate);

        $grossAmount = (float) ($order->final_amount ?? $order->amount ?? 0.0);

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

    /**
     * Main entry point to create revenue for a paid order.
     * Uses DB transaction and row locking to ensure idempotency.
     * Sets initial status to PENDING (or AVAILABLE if holdDays is 0) and calculates available_at based on refund hold period.
     */
    public function createRevenueForPaidOrder(int|Order $order): Revenue
    {
        $orderId = $order instanceof Order ? $order->id : (int) $order;

        return DB::transaction(function () use ($orderId, $order) {
            /** @var Order|null $orderModel */
            $orderModel = Order::query()
                ->lockForUpdate()
                ->find($orderId);

            if (! $orderModel) {
                throw new CourseInstructorMissingException("Order not found with ID: {$orderId}");
            }

            if (! in_array($orderModel->status, [Order::STATUS_PAID, 'paid', 'completed', 'success', 'paid_out'], true)) {
                Log::warning('Attempted revenue creation on unpaid order', [
                    'order_id' => $orderModel->id,
                    'status' => $orderModel->status,
                ]);
                throw new OrderNotPaidException("Revenue can only be generated for paid orders. Order {$orderModel->id} status is '{$orderModel->status}'.");
            }

            // Check idempotency before inserting
            $existingRevenue = Revenue::query()
                ->where('order_id', $orderModel->id)
                ->first();

            if ($existingRevenue) {
                return $existingRevenue;
            }

            $orderModel->loadMissing('course');
            if (! $orderModel->course || ! $orderModel->course->instructor_id) {
                throw new CourseInstructorMissingException("Order {$orderModel->id} is missing a valid course or course instructor.");
            }

            $calc = $this->calculateForOrder($orderModel);

            $orderModel->sale_source = $calc['sale_source'];
            $orderModel->commission_rule_id = $calc['rule_id'];
            $orderModel->save();

            if ($order instanceof Order) {
                $order->sale_source = $calc['sale_source'];
                $order->commission_rule_id = $calc['rule_id'];
                $order->save();
            }

            $earnedAt = $orderModel->paid_at ? Carbon::parse($orderModel->paid_at) : now();
            $availableAt = $earnedAt;
            $initialStatus = Revenue::STATUS_AVAILABLE;

            try {
                return Revenue::query()->create([
                    'instructor_id' => $orderModel->course->instructor_id,
                    'course_id' => $orderModel->course_id,
                    'order_id' => $orderModel->id,
                    'gross_amount' => $calc['gross_amount'],
                    'instructor_amount' => $calc['instructor_amount'],
                    'platform_fee_amount' => $calc['platform_fee_amount'],
                    'status' => $initialStatus,
                    'earned_at' => $earnedAt,
                    'available_at' => $availableAt,
                    'created_at' => now(),
                    'sale_source' => $calc['sale_source'],
                    'commission_rule_id' => $calc['rule_id'],
                    'commission_rule_code' => $calc['rule_code'],
                    'instructor_percent' => $calc['instructor_percent'],
                    'platform_percent' => $calc['platform_percent'],
                ]);
            } catch (Throwable $e) {
                // If unique constraint triggers in race condition
                $raceExisting = Revenue::query()->where('order_id', $orderModel->id)->first();
                if ($raceExisting) {
                    return $raceExisting;
                }

                Log::error('Failed creating revenue record', [
                    'order_id' => $orderModel->id,
                    'sale_source' => $calc['sale_source'],
                    'commission_rule_id' => $calc['rule_id'],
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Alias for createRevenueForPaidOrder to support alternative method signature.
     */
    public function calculateForPaidOrder(int|Order $order): Revenue
    {
        return $this->createRevenueForPaidOrder($order);
    }

    /**
     * Auto-sync revenue records for any paid orders that don't have one yet.
     */
    public function syncMissingPaidOrderRevenues(): int
    {
        $paidOrders = Order::query()
            ->whereIn('status', [Order::STATUS_PAID, 'paid', 'completed', 'success', 'paid_out'])
            ->whereNotNull('course_id')
            ->whereDoesntHave('revenue')
            ->get();

        $count = 0;
        foreach ($paidOrders as $order) {
            try {
                $this->createRevenueForPaidOrder($order);
                $count++;
            } catch (Throwable $e) {
                Log::error("Failed to auto-sync revenue for paid order {$order->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }

    /**
     * Release mature pending revenues to available status after refund hold period.
     */
    public function releaseAvailableRevenues(): int
    {
        return DB::transaction(function () {
            return Revenue::query()
                ->where('status', Revenue::STATUS_PENDING)
                ->update([
                    'status' => Revenue::STATUS_AVAILABLE,
                    'available_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }

    /**
     * Handle order refund safely without destroying revenue history.
     */
    public function handleRefund(int|Order $order): bool
    {
        $orderId = $order instanceof Order ? $order->id : (int) $order;

        return DB::transaction(function () use ($orderId) {
            $revenue = Revenue::query()
                ->where('order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (! $revenue) {
                return false;
            }

            if ($revenue->status === Revenue::STATUS_PENDING) {
                $revenue->update([
                    'status' => Revenue::STATUS_REFUNDED,
                    'updated_at' => now(),
                ]);
                return true;
            }

            if ($revenue->status === Revenue::STATUS_AVAILABLE) {
                $revenue->update([
                    'status' => Revenue::STATUS_REVERSED,
                    'updated_at' => now(),
                ]);
                return true;
            }

            if (in_array($revenue->status, [Revenue::STATUS_INCLUDED_IN_PAYOUT, Revenue::STATUS_SCHEDULED, Revenue::STATUS_PAID], true)) {
                // If revenue was already included in a paid/processing payout statement,
                // generate a negative adjustment revenue line for the next payout cycle.
                Revenue::query()->create([
                    'instructor_id' => $revenue->instructor_id,
                    'course_id' => $revenue->course_id,
                    'order_id' => $revenue->order_id,
                    'gross_amount' => -$revenue->gross_amount,
                    'instructor_amount' => -$revenue->instructor_amount,
                    'platform_fee_amount' => -$revenue->platform_fee_amount,
                    'status' => Revenue::STATUS_AVAILABLE,
                    'earned_at' => now(),
                    'available_at' => now(),
                    'created_at' => now(),
                    'sale_source' => $revenue->sale_source,
                    'commission_rule_id' => $revenue->commission_rule_id,
                    'commission_rule_code' => $revenue->commission_rule_code,
                    'instructor_percent' => $revenue->instructor_percent,
                    'platform_percent' => $revenue->platform_percent,
                ]);

                $revenue->update([
                    'status' => Revenue::STATUS_REVERSED,
                    'updated_at' => now(),
                ]);

                return true;
            }

            return false;
        });
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

        if ($source === 'marketplace') {
            return 'marketplace_default';
        }

        $validSources = [
            'marketplace_default',
            'platform_ads',
            'admin_campaign',
            'instructor_coupon',
            'instructor_referral',
        ];

        if ($source && ! in_array($source, $validSources, true)) {
            $hasCustomRule = Schema::hasTable('commission_rules') && CommissionRule::query()
                ->where('is_active', true)
                ->where(function ($query) use ($source) {
                    $columns = Schema::getColumnListing('commission_rules');
                    $first = true;
                    if (in_array('sale_channel', $columns, true)) {
                        $query->where('sale_channel', $source);
                        $first = false;
                    }
                    if (in_array('code', $columns, true)) {
                        if ($first) {
                            $query->where('code', $source);
                            $first = false;
                        } else {
                            $query->orWhere('code', $source);
                        }
                    }
                    if (in_array('type', $columns, true)) {
                        if ($first) {
                            $query->where('type', $source);
                        } else {
                            $query->orWhere('type', $source);
                        }
                    }
                })
                ->exists();

            if ($hasCustomRule) {
                return $source;
            }

            return 'marketplace_default';
        }

        return $source ?: 'marketplace_default';
    }

    public function resolveCommissionRule(string $saleSource): array
    {
        $presetRates = [
            'marketplace_default' => [70.0, 30.0],
            'platform_ads' => [37.0, 63.0],
            'admin_campaign' => [37.0, 63.0],
            'instructor_coupon' => [97.0, 3.0],
            'instructor_referral' => [97.0, 3.0],
        ];

        [$defaultIns, $defaultPlat] = $presetRates[$saleSource] ?? [70.0, 30.0];

        $rule = CommissionRule::query()
            ->where('sale_channel', $saleSource)
            ->orWhere('sale_channel', 'LIKE', $saleSource)
            ->first();

        if (! $rule && Schema::hasColumn('commission_rules', 'code')) {
            $rule = CommissionRule::query()->where('code', $saleSource)->first();
        }

        if (! $rule) {
            $rule = CommissionRule::query()->where('sale_channel', 'marketplace_default')->first();
        }

        if (! $rule) {
            $rule = CommissionRule::query()->first();
        }

        $insRate = $rule ? (float) ($rule->instructor_rate ?? $defaultIns) : $defaultIns;
        $platRate = $rule ? (float) ($rule->platform_rate ?? $defaultPlat) : $defaultPlat;
        $ruleId = $rule ? $rule->id : null;

        return [
            'instructor_percent' => $insRate,
            'platform_percent' => $platRate,
            'rule_id' => $ruleId,
            'rule_code' => $rule ? ($rule->sale_channel ?? $saleSource) : $saleSource,
        ];
    }

    private function validateRates(float $instructorRate, float $platformRate): void
    {
        if ($instructorRate < 0 || $instructorRate > 100 || $platformRate < 0 || $platformRate > 100) {
            throw new InvalidCommissionRuleException("Commission rates must be between 0 and 100. Got instructor: {$instructorRate}, platform: {$platformRate}.");
        }

        if (round($instructorRate + $platformRate, 2) !== 100.0) {
            throw new InvalidCommissionRuleException("Commission rates must sum to 100. Got sum: " . ($instructorRate + $platformRate));
        }
    }

    private function isInstructorOwnedCoupon(Order $order, Coupon $coupon, Course $course): bool
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
            $user = User::find($coupon->user_id);
            if ($user && $user->role === 'instructor') {
                return true;
            }
        }

        return false;
    }

    private function isAdminOrPlatformCoupon(Coupon $coupon): bool
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
        $user = User::find($coupon->user_id);
        if ($user && $user->role === 'admin') {
            return true;
        }

        return false;
    }
}
