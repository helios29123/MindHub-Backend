<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\Revenue;
use App\Repositories\Payment\CouponRepository;
use App\Repositories\Payment\EnrollmentRepository;
use App\Repositories\Payment\OrderRepository;
use App\Repositories\Payment\RevenueRepository;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentService
{
    private const PLATFORM_FEE_PERCENT = 30;
    private const DEFAULT_RETRY_PAYMENT_METHOD = 'vnpay';

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly RevenueRepository $revenueRepository,
        private readonly CouponRepository $couponRepository,
        private readonly EnrollmentAfterPaymentService $enrollmentAfterPaymentService,
        private readonly EnrollmentRepository $enrollmentRepository
    ) {
    }

    public function storePayment(array $paymentData, int $userId): Order
    {
        return DB::transaction(function () use ($paymentData, $userId) {
            $order = $this->orderRepository->findUserOrderForUpdate(
                (int) $paymentData['order_id'],
                $userId
            );

            if (! $order) {
                throw new BusinessException('Không tìm thấy đơn hàng hợp lệ.', 404);
            }

            if (
                $order->status === Order::STATUS_PAID ||
                $order->payment_status === Order::PAYMENT_PAID
            ) {
                throw new BusinessException('Đơn hàng đã được thanh toán.', 409);
            }

            if (
                in_array($order->status, [
                    Order::STATUS_FAILED,
                    Order::STATUS_CANCELLED,
                    Order::STATUS_EXPIRED,
                ], true) ||
                $order->payment_status === Order::PAYMENT_FAILED
            ) {
                throw new BusinessException('Đơn hàng không còn khả dụng để thanh toán.', 400);
            }

            if ($order->status !== Order::STATUS_PENDING) {
                throw new BusinessException('Đơn hàng không còn khả dụng để thanh toán.', 400);
            }

            if (
                ! empty($paymentData['transaction_code']) &&
                $this->orderRepository->existsTransactionForAnotherOrder(
                    $paymentData['transaction_code'],
                    $order->id
                )
            ) {
                throw new BusinessException('Mã giao dịch đã tồn tại.', 409);
            }

            $order->update([
                'payment_method' => $paymentData['payment_method'],
                'provider_transaction_id' => $paymentData['transaction_code'] ?? $order->provider_transaction_id,
                'payment_status' => Order::PAYMENT_PROCESSING,
            ]);

            return $order->fresh(['course', 'coupon']);
        });
    }

    public function retryPayment(array $retryData, int $userId): array
    {
        return DB::transaction(function () use ($retryData, $userId): array {
            $order = $this->orderRepository->findUserOrderWithCourseForUpdate(
                (int) $retryData['order_id'],
                $userId
            );

            if (! $order) {
                throw new BusinessException('Không tìm thấy đơn hàng.', 404);
            }

            $this->assertOrderCanRetryPayment($order, $userId);

            $paymentMethod = $retryData['payment_method']
                ?? $order->payment_method
                ?? self::DEFAULT_RETRY_PAYMENT_METHOD;

            try {
                $paymentUrl = match ($paymentMethod) {
                    'vnpay' => $this->createVnpayPaymentUrlForOrder($order),
                    default => throw new BusinessException('Cổng thanh toán tạm thời không khả dụng.', 503),
                };
            } catch (BusinessException $exception) {
                throw $exception;
            } catch (Throwable) {
                throw new BusinessException('Cổng thanh toán tạm thời không khả dụng.', 503);
            }

            return [
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'payment_url' => $paymentUrl,
            ];
        });
    }

    public function createVnpayPayment(array $paymentData, int $userId): string
    {
        return DB::transaction(function () use ($paymentData, $userId): string {
            $order = $this->orderRepository->findUserOrderForUpdate(
                (int) $paymentData['order_id'],
                $userId
            );

            if (! $order) {
                throw new BusinessException('Không tìm thấy đơn hàng hợp lệ.', 404);
            }

            if ($order->status === Order::STATUS_PAID || $order->payment_status === Order::PAYMENT_PAID) {
                throw new BusinessException('Đơn hàng đã được thanh toán.', 409);
            }

            if ($order->payment_status === Order::PAYMENT_PROCESSING) {
                throw new BusinessException('Đơn hàng đang được xử lý thanh toán.', 409);
            }

            if (
                in_array($order->status, [
                    Order::STATUS_FAILED,
                    Order::STATUS_CANCELLED,
                    Order::STATUS_EXPIRED,
                ], true)
                || $order->payment_status === Order::PAYMENT_FAILED
            ) {
                throw new BusinessException('Đơn hàng không còn khả dụng để thanh toán.', 400);
            }

            if ($order->status !== Order::STATUS_PENDING) {
                throw new BusinessException('Đơn hàng không còn khả dụng để thanh toán.', 400);
            }

            return $this->createVnpayPaymentUrlForOrder($order);
        });
    }

    public function handleWebhook(array $webhookData): Order
    {
        return DB::transaction(function () use ($webhookData) {
            $order = $this->orderRepository->findForUpdate((int) $webhookData['order_id']);

            if (! $order) {
                throw new BusinessException('Không tìm thấy đơn hàng.', 404);
            }

            if (
                ! empty($webhookData['transaction_code']) &&
                $this->orderRepository->existsTransactionForAnotherOrder(
                    $webhookData['transaction_code'],
                    $order->id
                )
            ) {
                throw new BusinessException('Mã giao dịch đã tồn tại.', 409);
            }

            if ($order->payment_status === Order::PAYMENT_PAID) {
                return $order->fresh(['course', 'coupon', 'enrollment', 'revenue']);
            }

            if (in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_EXPIRED], true)) {
                throw new BusinessException('Đơn hàng không còn khả dụng để cập nhật thanh toán.', 400);
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

    public function handleVnpayReturn(array $vnpayData): Order
    {
        $secureHash = $vnpayData['vnp_SecureHash'] ?? null;

        if (! $secureHash) {
            throw new BusinessException('Thiếu chữ ký VNPAY.', 400);
        }

        unset($vnpayData['vnp_SecureHash'], $vnpayData['vnp_SecureHashType']);

        ksort($vnpayData);

        $hashData = [];

        foreach ($vnpayData as $key => $value) {
            if ($value !== null && $value !== '') {
                $hashData[] = urlencode((string) $key) . '=' . urlencode((string) $value);
            }
        }

        $hashString = implode('&', $hashData);

        $calculatedHash = hash_hmac(
            'sha512',
            $hashString,
            trim((string) config('vnpay.hash_secret'))
        );

        if (! hash_equals($calculatedHash, $secureHash)) {
            throw new BusinessException('Chữ ký VNPAY không hợp lệ.', 400);
        }

        $transactionCode = $vnpayData['vnp_TxnRef'] ?? null;

        if (! $transactionCode) {
            throw new BusinessException('Thiếu mã giao dịch VNPAY.', 400);
        }

        $order = $this->orderRepository->findByProviderTransactionId($transactionCode);

        if (! $order) {
            throw new BusinessException('Không tìm thấy đơn hàng.', 404);
        }

        $paymentStatus = ($vnpayData['vnp_ResponseCode'] ?? null) === '00'
            ? Order::PAYMENT_PAID
            : Order::PAYMENT_FAILED;

        return $this->handleWebhook([
            'order_id' => $order->id,
            'transaction_code' => $transactionCode,
            'payment_status' => $paymentStatus,
            'paid_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function assertOrderCanRetryPayment(Order $order, int $userId): void
    {
        if (
            $order->status === Order::STATUS_PAID ||
            $order->payment_status === Order::PAYMENT_PAID
        ) {
            throw new BusinessException('Đơn hàng không thể thanh toán lại.', 409);
        }

        if (in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_EXPIRED], true)) {
            throw new BusinessException('Đơn hàng không thể thanh toán lại.', 409);
        }

        if (! in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_FAILED], true)) {
            throw new BusinessException('Đơn hàng không thể thanh toán lại.', 409);
        }

        if (! $order->course || $order->course->status !== 'published') {
            throw new BusinessException('Đơn hàng không thể thanh toán lại.', 409);
        }

        if ($this->enrollmentRepository->findByUserAndCourse($userId, (int) $order->course_id)) {
            throw new BusinessException('Đơn hàng không thể thanh toán lại.', 409);
        }
    }

    private function createVnpayPaymentUrlForOrder(Order $order): string
    {
        $amount = $this->getPayableAmount($order);

        if ($amount < 5000 || $amount >= 1000000000) {
            throw new BusinessException('Số tiền thanh toán không hợp lệ.', 400);
        }

        $transactionCode = $this->generateProviderTransactionId($order);

        $order->update([
            'payment_method' => 'vnpay',
            'provider_transaction_id' => $transactionCode,
            'payment_status' => Order::PAYMENT_PROCESSING,
        ]);

        $vnpayData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => trim((string) config('vnpay.tmn_code')),
            'vnp_Amount' => (string) ((int) round($amount * 100)),
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => request()->ip() ?: '127.0.0.1',
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $order->order_code,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => trim((string) (config('vnpay.return_url') ?: url('/api/payments/vnpay-return'))),
            'vnp_TxnRef' => $transactionCode,
            'vnp_ExpireDate' => now()->addMinutes(15)->format('YmdHis'),
        ];

        ksort($vnpayData);

        $hashData = [];
        $queryData = [];

        foreach ($vnpayData as $key => $value) {
            if ($value !== null && $value !== '') {
                $hashData[] = urlencode((string) $key) . '=' . urlencode((string) $value);
                $queryData[] = urlencode((string) $key) . '=' . urlencode((string) $value);
            }
        }

        $hashString = implode('&', $hashData);
        $queryString = implode('&', $queryData);

        $secureHash = hash_hmac(
            'sha512',
            $hashString,
            trim((string) config('vnpay.hash_secret'))
        );

        return trim((string) config('vnpay.url')) . '?' . $queryString . '&vnp_SecureHash=' . $secureHash;
    }

    private function getPayableAmount(Order $order): float
    {
        return (float) (
            $order->amount
            ?? $order->price_snapshot
            ?? 0
        );
    }

    private function generateProviderTransactionId(Order $order): string
    {
        do {
            $transactionCode = $order->order_code . '-PAY-' . now()->format('YmdHis') . '-' . random_int(100, 999);
        } while ($this->orderRepository->existsTransactionForAnotherOrder($transactionCode, $order->id));

        return $transactionCode;
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