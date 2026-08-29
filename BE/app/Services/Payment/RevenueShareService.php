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
    public function createRevenueForPaidOrder(int|Order $order): ?Revenue
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

            if ($orderModel->status !== Order::STATUS_PAID || $orderModel->payment_status !== Order::PAYMENT_PAID) {
                Log::warning('Attempted revenue creation on unpaid order', [
                    'order_id' => $orderModel->id,
                    'status' => $orderModel->status,
                ]);
                throw new OrderNotPaidException("Revenue can only be generated for paid orders. Order {$orderModel->id} status is '{$orderModel->status}'.");
            }

            if ((float) $orderModel->amount <= 0) {
                return null;
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

            $grossAmount = (float) $orderModel->amount;
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

    public function calculateForPaidOrder(int|Order $order): ?Revenue
    {
        return $this->createRevenueForPaidOrder($order);
    }

    public function syncMissingPaidOrderRevenues(): int
    {
        $paidOrders = Order::query()
            ->where('status', Order::STATUS_PAID)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->where('amount', '>', 0)
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

}
