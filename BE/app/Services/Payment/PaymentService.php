<?php

namespace App\Services\Payment;

use App\Models\Coupon;
use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public const PLATFORM_FEE_PERCENT = 30;

    public function __construct(
        private readonly PaymentGatewayInterface $gateway
    ) {
    }

    public function storePayment(array $data, ?int $userId = null): object
    {
        $userId = $userId ?: (int) Auth::id();

        return DB::transaction(function () use ($data, $userId): object {
            $orderId = (int) ($data['order_id'] ?? 0);

            if ($orderId <= 0) {
                throw new BusinessException('Thiếu mã đơn hàng.', 422);
            }

            $order = $this->findUserOrderForUpdate($orderId, $userId);

            $this->assertOrderCanBePaid($order);

            $providerTransactionId = $data['provider_transaction_id']
                ?? $data['transaction_id']
                ?? ('MANUAL-' . now()->format('YmdHis') . random_int(1000, 9999));

            $paymentMethod = $data['payment_method'] ?? 'manual';

            $this->markOrderAsPaid($order, $paymentMethod, $providerTransactionId);

            $paidOrder = DB::table('orders')
                ->where('id', $orderId)
                ->first();

            $this->applyPaidSideEffects($paidOrder);

            return DB::table('orders')
                ->where('id', $orderId)
                ->first();
        });
    }

    /**
     * Create SePay VietQR payment info.
     */
    public function createSepayPayment(array $validated, ?int $userId = null): array
    {
        $userId = $userId ?: (int) Auth::id();
        $orderId = (int) ($validated['order_id'] ?? 0);

        if ($orderId <= 0) {
            throw new BusinessException('Thiếu mã đơn hàng.', 422);
        }

        return DB::transaction(function () use ($orderId, $userId): array {
            $order = $this->findUserOrderForUpdate($orderId, $userId);

            $this->assertOrderCanCreatePayment($order);

            $amount = $this->getOrderAmount($order);
            if ($amount <= 0) {
                throw new BusinessException('Số tiền thanh toán không hợp lệ.', 422);
            }

            $bankCode = (string) (config('sepay.bank_code') ?: 'MBBank');
            $bankName = $bankCode === 'MBBank' ? 'MB Bank (Ngân hàng Quân Đội)' : $bankCode;
            $accountNumber = (string) (config('sepay.bank_account') ?: '0987654321');
            $accountName = (string) (config('sepay.account_name') ?: 'MINDHUB E-LEARNING');
            $transferContent = $order->order_code ?? ('MH' . $order->id);

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'payment_method' => 'sepay',
                    'provider_transaction_id' => $transferContent,
                    'updated_at' => now(),
                ]);

            $bankShortCode = str_ireplace(['MBBank', 'MB Bank'], 'MB', $bankCode);
            $qrUrl = "https://img.vietqr.io/image/{$bankShortCode}-{$accountNumber}-compact2.png?amount=" . (int) round($amount)
                . "&addInfo=" . urlencode($transferContent)
                . "&accountName=" . urlencode($accountName);

            return [
                'order_id' => (int) $order->id,
                'order_code' => $transferContent,
                'amount' => $amount,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_name' => $accountName,
                'transfer_content' => $transferContent,
                'qr_url' => $qrUrl,
                'payment_method' => 'sepay',
            ];
        });
    }

    /**
     * Create VNPAY payment redirect URL.
     */
    public function createVnpayPayment(array $validated, ?int $userId = null): array
    {
        $userId = $userId ?: (int) Auth::id();
        $orderId = (int) ($validated['order_id'] ?? 0);

        if ($orderId <= 0) {
            throw new BusinessException('Thiếu mã đơn hàng.', 422);
        }

        return DB::transaction(function () use ($orderId, $userId): array {
            $order = $this->findUserOrderForUpdate($orderId, $userId);

            $this->assertOrderCanCreatePayment($order);

            $amount = $this->getOrderAmount($order);
            if ($amount <= 0) {
                throw new BusinessException('Số tiền thanh toán không hợp lệ.', 422);
            }

            $txnRef = $this->generateVnpayTxnRef($order);

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'payment_method' => 'vnpay',
                    'provider_transaction_id' => $txnRef,
                    'updated_at' => now(),
                ]);

            $paymentUrl = $this->buildVnpayPaymentUrl($order, $txnRef, $amount);

            return [
                'order_id' => (int) $order->id,
                'order_code' => $order->order_code ?? null,
                'amount' => $amount,
                'payment_url' => $paymentUrl,
                'payment_method' => 'vnpay',
                'txn_ref' => $txnRef,
            ];
        });
    }

    /**
     * Handle VNPAY Return URL after payment.
     */
    public function vnpayReturn(array $payload): array
    {
        if (empty($payload)) {
            throw new BusinessException('Không có dữ liệu phản hồi từ VNPAY.', 400);
        }

        $isValidSignature = $this->verifyVnpaySignature($payload);
        if (! $isValidSignature) {
            throw new BusinessException('Chữ ký xác thực VNPAY không hợp lệ.', 400);
        }

        $txnRef = (string) ($payload['vnp_TxnRef'] ?? '');
        $responseCode = (string) ($payload['vnp_ResponseCode'] ?? '');
        $transactionStatus = (string) ($payload['vnp_TransactionStatus'] ?? '');
        $transactionNo = (string) ($payload['vnp_TransactionNo'] ?? $txnRef);

        if (empty($txnRef)) {
            throw new BusinessException('Thiếu thông tin mã giao dịch VNPAY.', 400);
        }

        return DB::transaction(function () use ($txnRef, $responseCode, $transactionStatus, $transactionNo, $payload): array {
            $order = DB::table('orders')
                ->where('provider_transaction_id', $txnRef)
                ->orWhere('order_code', $txnRef)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                preg_match('/^(\d+)/', $txnRef, $matches);
                if (! empty($matches[1])) {
                    $order = DB::table('orders')
                        ->where('id', (int) $matches[1])
                        ->lockForUpdate()
                        ->first();
                }
            }

            if (! $order) {
                throw new BusinessException('Không tìm thấy đơn hàng tương ứng với giao dịch VNPAY.', 404);
            }

            $isSuccess = ($responseCode === '00' && ($transactionStatus === '' || $transactionStatus === '00'));

            if ($isSuccess && ($order->status ?? null) !== Order::STATUS_PAID) {
                $this->markOrderAsPaid($order, 'vnpay', $transactionNo);

                $paidOrder = DB::table('orders')
                    ->where('id', $order->id)
                    ->first();

                $this->applyPaidSideEffects($paidOrder);
            }

            return [
                'success' => $isSuccess,
                'message' => $isSuccess ? 'Thanh toán VNPAY thành công.' : 'Giao dịch VNPAY không thành công hoặc bị hủy.',
                'order_id' => (int) $order->id,
                'order_code' => $order->order_code ?? null,
                'response_code' => $responseCode,
                'transaction_no' => $transactionNo,
            ];
        });
    }

    public function handleSepayWebhook(array $payload): array
    {
        $this->assertSepayWebhookSignature();

        $content = (string) ($payload['content'] ?? $payload['description'] ?? $payload['memo'] ?? $payload['subAccount'] ?? '');
        $transferAmount = (float) ($payload['transferAmount'] ?? $payload['amount'] ?? 0);
        $referenceCode = (string) ($payload['referenceCode'] ?? $payload['id'] ?? $payload['transaction_id'] ?? $payload['code'] ?? '');

        if (empty($content)) {
            return ['success' => false, 'message' => 'Nội dung chuyển khoản trống.'];
        }

        return DB::transaction(function () use ($content, $transferAmount, $referenceCode): array {
            $order = DB::table('orders')
                ->where('status', '!=', Order::STATUS_PAID)
                ->where(function ($q) use ($content) {
                    $q->whereRaw('? LIKE CONCAT("%", order_code, "%")', [$content])
                      ->orWhereRaw('? LIKE CONCAT("%", provider_transaction_id, "%")', [$content])
                      ->orWhere('provider_transaction_id', $content);
                })
                ->lockForUpdate()
                ->first();

            if (! $order) {
                $normalizedContent = strtoupper((string) preg_replace('/[^a-zA-Z0-9]/', '', $content));
                $candidates = DB::table('orders')
                    ->where('status', '!=', Order::STATUS_PAID)
                    ->lockForUpdate()
                    ->get();

                foreach ($candidates as $candidate) {
                    $normCode = strtoupper((string) preg_replace('/[^a-zA-Z0-9]/', '', (string) $candidate->order_code));
                    $normTxn = strtoupper((string) preg_replace('/[^a-zA-Z0-9]/', '', (string) $candidate->provider_transaction_id));
                    if (($normCode !== '' && str_contains($normalizedContent, $normCode))
                        || ($normTxn !== '' && str_contains($normalizedContent, $normTxn))) {
                        $order = $candidate;
                        break;
                    }
                }
            }

            if (! $order) {
                return ['success' => false, 'message' => 'Không tìm thấy đơn hàng cho nội dung: ' . $content];
            }

            $orderAmount = $this->getOrderAmount($order);

            if (abs($transferAmount - $orderAmount) > 0.01) {
                throw new BusinessException('Số tiền thanh toán không khớp với đơn hàng.', 422);
            }

            if (($order->status ?? null) !== Order::STATUS_PAID) {
                $this->assertOrderCanBePaid($order);
                $this->markOrderAsPaid($order, 'sepay', $referenceCode ?: ('SEPAY-' . time()));

                $paidOrder = DB::table('orders')
                    ->where('id', $order->id)
                    ->first();

                $this->applyPaidSideEffects($paidOrder);
            }

            return [
                'success' => true,
                'message' => 'Xử lý thanh toán SePay thành công.',
                'order_id' => $order->id,
                'order_code' => $order->order_code ?? null,
            ];
        });
    }

    public function webhook(array $payload): array
    {
        $details = $this->gateway->handleWebhook($payload);
        
        $orderId = $details['order_id'];
        $amountPaid = $details['amount'];
        $providerTransactionId = $details['provider_transaction_id'];

        return DB::transaction(function () use ($orderId, $amountPaid, $providerTransactionId, $payload): array {
            $order = DB::table('orders')
                ->where('id', $orderId)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                throw new BusinessException('Không tìm thấy đơn hàng.', 404);
            }

            $expectedAmount = $this->getOrderAmount($order);

            if (abs($amountPaid - $expectedAmount) > 0.01) {
                throw new BusinessException('Số tiền thanh toán không khớp với đơn hàng.', 422);
            }

            if (($order->status ?? null) !== Order::STATUS_PAID) {
                $this->assertOrderCanBePaid($order);
                $this->markOrderAsPaid($order, 'sepay', $providerTransactionId);

                $paidOrder = DB::table('orders')
                    ->where('id', $order->id)
                    ->first();

                $this->applyPaidSideEffects($paidOrder);
            }

            $latestOrder = DB::table('orders')
                ->where('id', $order->id)
                ->first();

            return [
                'success' => true,
                'message' => 'Xác nhận thanh toán SePay thành công.',
                'order_id' => (int) $order->id,
                'order_code' => $order->order_code ?? null,
                'order' => $latestOrder,
                'sepay' => $payload,
            ];
        });
    }

    private function findUserOrderForUpdate(int $orderId, int $userId): object
    {
        $query = DB::table('orders')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->lockForUpdate();

$order = $query->first();

        if (! $order) {
            throw new BusinessException('Không tìm thấy đơn hàng.', 404);
        }

        return $order;
    }

    private function assertOrderCanCreatePayment(object $order): void
    {
        if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
            throw new BusinessException('Đơn hàng không ở trạng thái có thể thanh toán.', 409);
        }
        if ($order->payment_status !== Order::PAYMENT_PENDING) {
            throw new BusinessException('Trạng thái thanh toán của đơn hàng không hợp lệ.', 409);
        }
    }

    private function assertOrderCanBePaid(object $order): void
    {
        if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
            throw new BusinessException('Đơn hàng không ở trạng thái có thể thanh toán.', 409);
        }
        if ($order->payment_status !== Order::PAYMENT_PENDING) {
            throw new BusinessException('Trạng thái thanh toán của đơn hàng không hợp lệ.', 409);
        }
    }

    private function assertInstructorCanPayCourseOrder(object $order, int $userId): void
    {
        if (empty($order->course_id)) {
            throw new BusinessException('Đơn mua khóa học không hợp lệ.', 422);
        }
        $course = DB::table('courses')->where('id', $order->course_id)->first();
        if (! $course) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }
        if ((int) $course->instructor_id === $userId) {
            throw new BusinessException('Bạn không thể thanh toán khóa học của chính mình.', 409);
        }
    }

    private function markOrderAsPaid(object $order, string $paymentMethod, string $providerTransactionId): void
    {
        DB::table('orders')->where('id', $order->id)->update([
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => $paymentMethod,
            'provider_transaction_id' => $providerTransactionId,
            'paid_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function applyPaidSideEffects(?object $order): void
    {
        if (! $order) {
            return;
        }
        $orderModel = Order::query()->find($order->id);
        if (! $orderModel || ! $orderModel->isPaid()) {
            throw new BusinessException('Order chưa đủ điều kiện xử lý sau thanh toán.', 409);
        }
        app(EnrollmentAfterPaymentService::class)->createEnrollmentAfterPayment($orderModel);

        if ((float) $orderModel->amount > 0) {
            app(RevenueShareService::class)->createRevenueForPaidOrder($orderModel);
        }

        $this->finalizeCouponUsage($orderModel);

        DB::table('wishlist')
            ->where('user_id', $orderModel->user_id)
            ->where('course_id', $orderModel->course_id)
            ->delete();
    }

    private function finalizeCouponUsage(Order $order): void
    {
        if (!$order->coupon_id) {
            return;
        }

        DB::transaction(function () use ($order): void {
            $coupon = Coupon::query()
                ->whereKey($order->coupon_id)
                ->lockForUpdate()
                ->first();

            if (!$coupon || $coupon->campaign_type !== 'discount') {
                return;
            }

            $usageLimit = $coupon->usage_limit !== null
                ? (int) $coupon->usage_limit
                : null;

            if ($usageLimit !== null && (int) $coupon->used_count >= $usageLimit) {
                if ($coupon->status !== 'used_up') {
                    $coupon->status = 'used_up';
                    $coupon->save();
                }

                return;
            }

            $coupon->used_count = (int) $coupon->used_count + 1;

            if ($usageLimit !== null && (int) $coupon->used_count >= $usageLimit) {
                $coupon->used_count = $usageLimit;
                $coupon->status = 'used_up';
            }

            $coupon->save();
        });
    }

    private function assertSepayWebhookSignature(): void
    {
        $apiToken = (string) config('sepay.api_token');
        $secret = (string) config('sepay.webhook_secret');

        // 1. Check Bearer / ApiKey Authorization header (SePay & GPM)
        $authHeader = (string) request()->header('Authorization');
        if ($apiToken !== '' && $authHeader !== '') {
            $token = trim(str_ireplace(['Bearer ', 'Apikey '], '', $authHeader));
            if (hash_equals($apiToken, $token)) {
                return;
            }
        }

        $apiKeyHeader = (string) (request()->header('X-Api-Key') ?? request()->header('X-API-KEY') ?? request()->header('apikey'));
        if ($apiToken !== '' && $apiKeyHeader !== '') {
            if (hash_equals($apiToken, trim($apiKeyHeader))) {
                return;
            }
        }

        // 2. Check X-SePay-Signature header if webhook_secret is configured
        $signature = (string) request()->header('X-SePay-Signature');
        if ($secret !== '' && $signature !== '') {
            $expected = hash_hmac('sha256', request()->getContent(), $secret);
            if (hash_equals($expected, $signature)) {
                return;
            }
        }

        // 3. In non-production, allow token query or body param, or bypass if not configured
        if (config('app.env') !== 'production') {
            $providedKey = request()->input('api_key') ?? request()->input('token');
            if ($apiToken !== '' && $providedKey && hash_equals($apiToken, (string) $providedKey)) {
                return;
            }
            if ($secret === '') {
                return;
            }
        }

        throw new BusinessException('Xác thực Webhook thanh toán thất bại. Thiếu chữ ký hoặc API key không hợp lệ.', 401);
    }

    private function getOrderAmount(object $order): float
    {
        return (float) ($order->amount ?? 0);
    }

    private function generateVnpayTxnRef(object $order): string
    {
        return $order->id . date('His') . random_int(100, 999);
    }

    private function buildVnpayPaymentUrl(object $order, string $txnRef, float $amount): string
    {
        $vnpUrl = (string) (config('services.vnpay.url') ?: 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $tmnCode = (string) config('services.vnpay.tmn_code');
        $hashSecret = (string) config('services.vnpay.hash_secret');
        $returnUrl = (string) (config('services.vnpay.return_url') ?: url('/api/payments/vnpay-return'));

        if (empty($tmnCode) || empty($hashSecret)) {
            throw new BusinessException('Chưa cấu hình VNPAY_TMN_CODE hoặc VNPAY_HASH_SECRET.', 500);
        }

        $clientIp = request()->ip();
        if (! $clientIp || $clientIp === '::1' || ! filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $clientIp = '127.0.0.1';
        }

        $vnpParams = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => (int) round($amount * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => 'Thanh toan don hang ' . ($order->order_code ?? $order->id),
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_IpAddr' => $clientIp,
            'vnp_CreateDate' => date('YmdHis'),
        ];

        ksort($vnpParams);

        $query = "";
        $i = 0;
        $hashData = "";
        foreach ($vnpParams as $key => $value) {
            if ($value !== null && $value !== '') {
                if ($i == 1) {
                    $hashData .= '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
                } else {
                    $hashData .= urlencode((string)$key) . "=" . urlencode((string)$value);
                    $i = 1;
                }
                $query .= urlencode((string)$key) . "=" . urlencode((string)$value) . '&';
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $hashSecret);

        return $vnpUrl . '?' . $query . 'vnp_SecureHash=' . $secureHash;
    }

    private function verifyVnpaySignature(array $params): bool
    {
        $hashSecret = (string) config('services.vnpay.hash_secret');

        if (empty($hashSecret)) {
            throw new BusinessException('Chưa cấu hình VNPAY_HASH_SECRET.', 500);
        }

        $secureHash = $params['vnp_SecureHash'] ?? null;

        if (! $secureHash) {
            return false;
        }

        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

        $hashData = $this->buildVnpayHashData($params);
        $calculatedHash = hash_hmac('sha512', $hashData, $hashSecret);

        return hash_equals((string) $secureHash, $calculatedHash);
    }

    private function buildVnpayHashData(array $params): string
    {
        ksort($params);

        $hashData = [];

        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $hashData[] = urlencode((string) $key) . '=' . urlencode((string) $value);
        }

        return implode('&', $hashData);
    }
}
