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

            $rule = CommissionRule::find($orderModel->commission_rule_id);
            if (!$rule) {
                throw new CommissionRuleNotFoundException("Commission rule not found for order {$orderModel->id}");
            }

            $grossAmount = (float) ($orderModel->final_amount ?? $orderModel->amount ?? 0.0);
            $instructorRate = (float) $rule->instructor_rate;
            $instructorAmount = round($grossAmount * $instructorRate, 2);
            $platformFeeAmount = $grossAmount - $instructorAmount;

            $earnedAt = $orderModel->paid_at ? Carbon::parse($orderModel->paid_at) : now();

            try {
                return Revenue::query()->create([
                    'instructor_id' => $orderModel->course->instructor_id,
                    'course_id' => $orderModel->course_id,
                    'order_id' => $orderModel->id,
                    'gross_amount' => $grossAmount,
                    'instructor_amount' => $instructorAmount,
                    'platform_fee_amount' => $platformFeeAmount,
                    'commission_rule_id' => $rule->id,
                    'earned_at' => $earnedAt,
                ]);
            } catch (Throwable $e) {
                $raceExisting = Revenue::query()->where('order_id', $orderModel->id)->first();
                if ($raceExisting) {
                    return $raceExisting;
                }

                Log::error('Failed creating revenue record', [
                    'order_id' => $orderModel->id,
                    'commission_rule_id' => $rule->id,
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    public function calculateForPaidOrder(int|Order $order): Revenue
    {
        return $this->createRevenueForPaidOrder($order);
    }

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

    public function releaseAvailableRevenues(): int
    {
        // DEFERRED TO NEXT PHASE: Redesign withdrawal logic without revenue status
        return 0;
    }

    public function handleRefund(int|Order $order): bool
    {
        // DEFERRED TO NEXT PHASE: Redesign refund logic without revenue status
        return false;
    }
}
