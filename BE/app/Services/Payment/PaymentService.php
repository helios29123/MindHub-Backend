<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\Revenue;
use App\Repositories\Payment\CouponRepository;
use App\Repositories\Payment\OrderRepository;
use App\Repositories\Payment\RevenueRepository;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    private const PLATFORM_FEE_PERCENT = 30;

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly RevenueRepository $revenueRepository,
        private readonly CouponRepository $couponRepository,
        private readonly EnrollmentAfterPaymentService $enrollmentAfterPaymentService
    ) {
    }

    public function storePayment(array $paymentData, int $userId): Order
    {
        return DB::transaction(function () use ($paymentData, $userId) {
            $order = $this->orderRepository->findUserOrderForUpdate(
                $paymentData['order_id'],
                $userId
            );

            if (!$order) {
                throw new BusinessException('Khﾃｴng tﾃｬm th蘯･y ﾄ柁｡n hﾃng.', 404);
            }

            if ($order->status !== Order::STATUS_PENDING) {
                throw new BusinessException('ﾄ脆｡n hﾃng khﾃｴng cﾃｲn kh蘯｣ d盻･ng ﾄ黛ｻ・thanh toﾃ｡n.', 400);
            }

            if (
                !empty($paymentData['transaction_code'])
                && $this->orderRepository->existsTransactionForAnotherOrder(
                    $paymentData['transaction_code'],
                    $order->id
                )
            ) {
                throw new BusinessException('Mﾃ｣ giao d盻議h ﾄ妥｣ t盻渡 t蘯｡i.', 409);
            }

            $order->update([
                'payment_method' => $paymentData['payment_method'],
                'provider_transaction_id' => $paymentData['transaction_code'] ?? $order->provider_transaction_id,
                'payment_status' => Order::PAYMENT_PROCESSING,
            ]);

            return $order->fresh(['course', 'coupon']);
        });
    }

    public function handleWebhook(array $webhookData): Order
    {
        return DB::transaction(function () use ($webhookData) {
            $order = $this->orderRepository->findForUpdate($webhookData['order_id']);

            if (!$order) {
                throw new BusinessException('Khﾃｴng tﾃｬm th蘯･y ﾄ柁｡n hﾃng.', 404);
            }

            if (
                !empty($webhookData['transaction_code'])
                && $this->orderRepository->existsTransactionForAnotherOrder(
                    $webhookData['transaction_code'],
                    $order->id
                )
            ) {
                throw new BusinessException('Mﾃ｣ giao d盻議h ﾄ妥｣ t盻渡 t蘯｡i.', 409);
            }

            if ($order->payment_status === Order::PAYMENT_PAID) {
                return $order->fresh(['course', 'coupon', 'enrollment', 'revenue']);
            }

            if (in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_EXPIRED], true)) {
                throw new BusinessException('ﾄ脆｡n hﾃng khﾃｴng cﾃｲn kh蘯｣ d盻･ng ﾄ黛ｻ・c蘯ｭp nh蘯ｭt thanh toﾃ｡n.', 400);
            }

            if ($webhookData['payment_status'] === Order::PAYMENT_FAILED) {
                $order->update([
                    'status' => Order::STATUS_FAILED,
                    'payment_status' => Order::PAYMENT_FAILED,
                    'provider_transaction_id' => $webhookData['transaction_code'] ?? $order->provider_transaction_id,
                ]);

                return $order->fresh(['course', 'coupon']);
            }

            $order->update([
                'status' => Order::STATUS_PAID,
                'payment_status' => Order::PAYMENT_PAID,
                'paid_at' => $webhookData['paid_at'],
                'provider_transaction_id' => $webhookData['transaction_code'] ?? $order->provider_transaction_id,
            ]);

            $paidOrder = $order->fresh(['course', 'coupon']);

            $this->enrollmentAfterPaymentService->createEnrollmentAfterPayment($paidOrder);
            $this->createRevenueIfNotExists($paidOrder);

            if ($paidOrder->coupon_id !== null && $paidOrder->coupon !== null) {
                $this->couponRepository->incrementUsedCount($paidOrder->coupon);
            }

            return $paidOrder->fresh(['course', 'coupon', 'enrollment', 'revenue']);
        });
    }

    private function createRevenueIfNotExists(Order $order): void
    {
        if ($this->revenueRepository->findByOrderId($order->id)) {
            return;
        }

        $grossAmount = (float) $order->amount;
        $platformFeeAmount = round($grossAmount * self::PLATFORM_FEE_PERCENT / 100, 2);
        $instructorAmount = $grossAmount - $platformFeeAmount;

        $this->revenueRepository->create([
            'instructor_id' => $order->course->instructor_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'gross_amount' => $grossAmount,
            'instructor_amount' => $instructorAmount,
            'platform_fee_amount' => $platformFeeAmount,
            'status' => Revenue::STATUS_PENDING,
            'earned_at' => now(),
            'created_at' => now(),
        ]);
    }
}
