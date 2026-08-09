<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

            if (($order->payment_status ?? null) === Order::PAYMENT_PAID) {
                throw new BusinessException('Đơn hàng này đã được thanh toán.', 422);
            }

            $amount = $this->getOrderAmount($order);
            if ($amount <= 0) {
                throw new BusinessException('Số tiền thanh toán không hợp lệ.', 422);
            }

            $bankName = config('sepay.bank_code', 'MBBank');
            $accountNumber = config('sepay.bank_account', '0987654321');
            $accountName = config('sepay.account_name', 'MINDHUB E-LEARNING');
            $transferContent = $order->order_code ?? ('MH' . $order->id);

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'payment_method' => 'sepay',
                    'provider_transaction_id' => $transferContent,
                    'updated_at' => now(),
                ]);

            $qrUrl = "https://qr.sepay.vn/img?bank=" . urlencode($bankName)
                . "&acc=" . urlencode($accountNumber)
                . "&template=compact&amount=" . (int) round($amount)
                . "&des=" . urlencode($transferContent);

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

    public function handleSepayWebhook(array $payload): array
    {
        $content = (string) ($payload['content'] ?? '');
        $transferAmount = (float) ($payload['transferAmount'] ?? 0);
        $referenceCode = (string) ($payload['referenceCode'] ?? $payload['id'] ?? '');

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
                preg_match('/MH\d+/i', $content, $matches);
                if (! empty($matches[0])) {
                    $order = DB::table('orders')
                        ->where('order_code', $matches[0])
                        ->orWhere('provider_transaction_id', $matches[0])
                        ->lockForUpdate()
                        ->first();
                }
            }

            if (! $order) {
                return ['success' => false, 'message' => 'Không tìm thấy đơn hàng cho nội dung: ' . $content];
            }

            $orderAmount = $this->getOrderAmount($order);
            if ($transferAmount < ($orderAmount - 100)) {
                return ['success' => false, 'message' => 'Số tiền chuyển khoản chưa đủ.'];
            }

            if (($order->payment_status ?? null) !== Order::PAYMENT_PAID) {
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

    public function confirmSepayPayment(array $validated, int $userId): array
    {
        $orderId = (int) $validated['order_id'];

        return DB::transaction(function () use ($orderId, $userId): array {
            $order = $this->findUserOrderForUpdate($orderId, $userId);

            if (($order->payment_status ?? null) !== Order::PAYMENT_PAID) {
                $this->markOrderAsPaid($order, 'sepay', 'SEPAY-CONFIRM-' . time());

                $paidOrder = DB::table('orders')
                    ->where('id', $order->id)
                    ->first();

                $this->applyPaidSideEffects($paidOrder);
            }

            return [
                'success' => true,
                'message' => 'Xác nhận thanh toán thành công.',
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

            if ($amountPaid < $expectedAmount) {
                // If paid amount is less, we shouldn't mark it as paid, or mark it as partial
                throw new BusinessException('Số tiền thanh toán không đủ.', 422);
            }

            if (($order->payment_status ?? null) !== Order::PAYMENT_PAID) {
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
                'order_type' => $this->resolveOrderType($order),
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

        if (Schema::hasColumn('orders', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $order = $query->first();

        if (! $order) {
            throw new BusinessException('Không tìm thấy đơn hàng.', 404);
        }

        return $order;
    }

    private function assertOrderCanCreatePayment(object $order): void
    {
        $status = (string) ($order->status ?? '');
        $paymentStatus = (string) ($order->payment_status ?? '');

        if ($status !== Order::STATUS_PENDING) {
            throw new BusinessException('Đơn hàng không ở trạng thái có thể thanh toán.', 409);
        }

        if ($paymentStatus === Order::PAYMENT_PAID) {
            throw new BusinessException('Đơn hàng đã được thanh toán.', 409);
        }

        if ($paymentStatus === Order::PAYMENT_FAILED) {
            throw new BusinessException('Đơn hàng đã thanh toán thất bại, vui lòng tạo đơn mới.', 409);
        }

        if ($paymentStatus === Order::PAYMENT_PROCESSING) {
            throw new BusinessException('Đơn hàng đang được xử lý thanh toán.', 409);
        }

        if (! in_array($paymentStatus, [Order::PAYMENT_UNPAID, ''], true)) {
            throw new BusinessException('Trạng thái thanh toán của đơn hàng không hợp lệ.', 409);
        }
    }

    private function assertOrderCanBePaid(object $order): void
    {
        $status = (string) ($order->status ?? '');
        $paymentStatus = (string) ($order->payment_status ?? '');

        if ($status !== Order::STATUS_PENDING) {
            throw new BusinessException('Đơn hàng không ở trạng thái có thể thanh toán.', 409);
        }

        if ($paymentStatus === Order::PAYMENT_PAID) {
            throw new BusinessException('Đơn hàng đã được thanh toán.', 409);
        }

        if ($paymentStatus === Order::PAYMENT_FAILED) {
            throw new BusinessException('Đơn hàng đã thanh toán thất bại.', 409);
        }

        if (! in_array($paymentStatus, [Order::PAYMENT_UNPAID, Order::PAYMENT_PROCESSING, ''], true)) {
            throw new BusinessException('Trạng thái thanh toán của đơn hàng không hợp lệ.', 409);
        }
    }

    private function assertInstructorCanPayCourseOrder(object $order, int $userId): void
    {
        if (empty($order->course_id)) {
            throw new BusinessException('Đơn mua khóa học không hợp lệ.', 422);
        }

        $courseQuery = DB::table('courses')
            ->where('id', $order->course_id);

        if (Schema::hasColumn('courses', 'deleted_at')) {
            $courseQuery->whereNull('deleted_at');
        }

        $course = $courseQuery->first();

        if (! $course) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }

        if ((int) ($course->instructor_id ?? 0) === $userId) {
            throw new BusinessException('Bạn không thể thanh toán khóa học của chính mình.', 409);
        }
    }

    private function markOrderAsPaid(object $order, string $paymentMethod, string $providerTransactionId): void
    {
        DB::table('orders')
            ->where('id', $order->id)
            ->update([
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

        $orderType = $this->resolveOrderType($order);

        if ($orderType === Order::TYPE_INSTRUCTOR_CREDIT) {
            $this->addCreditsAfterInstructorCreditOrderPaid($order);
            return;
        }

        if ($orderType === Order::TYPE_COURSE_PURCHASE) {
            $this->createEnrollmentAfterCourseOrderPaid($order);
            $this->createRevenueAfterCourseOrderPaid($order);
        }
    }

    private function addCreditsAfterInstructorCreditOrderPaid(object $order): void
    {
        $instructorId = (int) $order->user_id;

        $credits = (int) ($order->package_snapshot_credits ?? 0);

        if ($credits <= 0 && ! empty($order->credit_package_id)) {
            $package = DB::table('course_credit_packages')
                ->where('id', $order->credit_package_id)
                ->first();

            $credits = (int) ($package->credits ?? 0);
        }

        if ($credits <= 0) {
            throw new BusinessException('Số lượt trong đơn mua gói không hợp lệ.', 422);
        }

        $alreadyApplied = DB::table('instructor_credit_transactions')
            ->where('instructor_id', $instructorId)
            ->where('order_id', $order->id)
            ->where('type', 'purchase')
            ->exists();

        if ($alreadyApplied) {
            return;
        }

        $balance = DB::table('instructor_course_credits')
            ->where('instructor_id', $instructorId)
            ->lockForUpdate()
            ->first();

        if (! $balance) {
            DB::table('instructor_course_credits')->insert([
                'instructor_id' => $instructorId,
                'total_credits' => 0,
                'used_credits' => 0,
                'remaining_credits' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $balance = DB::table('instructor_course_credits')
                ->where('instructor_id', $instructorId)
                ->lockForUpdate()
                ->first();
        }

        $balanceBefore = (int) ($balance->remaining_credits ?? 0);
        $balanceAfter = $balanceBefore + $credits;

        DB::table('instructor_course_credits')
            ->where('instructor_id', $instructorId)
            ->update([
                'total_credits' => (int) ($balance->total_credits ?? 0) + $credits,
                'remaining_credits' => $balanceAfter,
                'updated_at' => now(),
            ]);

        DB::table('instructor_credit_transactions')->insert([
            'instructor_id' => $instructorId,
            'order_id' => $order->id,
            'course_id' => null,
            'type' => 'purchase',
            'credits' => $credits,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'note' => 'Cộng lượt sau khi thanh toán gói tạo khóa học.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createEnrollmentAfterCourseOrderPaid(object $order): void
    {
        if (empty($order->course_id)) {
            return;
        }

        $query = DB::table('enrollments')
            ->where('user_id', $order->user_id)
            ->where('course_id', $order->course_id);

        if (Schema::hasColumn('enrollments', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if ($query->exists()) {
            return;
        }

        $insertData = [
            'user_id' => $order->user_id,
            'course_id' => $order->course_id,
        ];

        if (Schema::hasColumn('enrollments', 'order_id')) {
            $insertData['order_id'] = $order->id;
        }

        if (Schema::hasColumn('enrollments', 'status')) {
            $insertData['status'] = 'active';
        }

        if (Schema::hasColumn('enrollments', 'progress_percent')) {
            $insertData['progress_percent'] = 0;
        }

        if (Schema::hasColumn('enrollments', 'created_at')) {
            $insertData['created_at'] = now();
        }

        if (Schema::hasColumn('enrollments', 'updated_at')) {
            $insertData['updated_at'] = now();
        }

        DB::table('enrollments')->insert($insertData);

        try {
            $user = \App\Models\User::find($order->user_id);
            $course = \App\Models\Course::with('instructor')->find($order->course_id);

            if ($user && $course && ! empty($user->email)) {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\CourseWelcomeMail($user, $course, $order));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send CourseWelcomeMail: ' . $e->getMessage());
        }
    }

    private function createRevenueAfterCourseOrderPaid(object $order): void
    {
        if (! Schema::hasTable('revenues')) {
            return;
        }

        if (empty($order->course_id)) {
            return;
        }

        $orderModel = Order::query()->find($order->id);
        if (!$orderModel) {
            return;
        }

        app(RevenueShareService::class)->createRevenueForPaidOrder($orderModel);
    }

    private function resolveOrderType(object $order): string
    {
        if (! empty($order->order_type)) {
            return (string) $order->order_type;
        }

        if (! empty($order->credit_package_id)) {
            return Order::TYPE_INSTRUCTOR_CREDIT;
        }

        return Order::TYPE_COURSE_PURCHASE;
    }

    private function getOrderAmount(object $order): float
    {
        $amount = $order->final_amount
            ?? $order->amount
            ?? $order->price_snapshot
            ?? $order->price
            ?? 0;

        return (float) $amount;
    }

    private function generateVnpayTxnRef(object $order): string
    {
        return $order->id . date('His') . random_int(100, 999);
    }

    private function buildVnpayPaymentUrl(object $order, string $txnRef, float $amount): string
    {
        $vnpUrl = config('vnpay.url')
            ?: config('services.vnpay.url')
            ?: env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');

        $tmnCode = config('vnpay.tmn_code')
            ?: config('services.vnpay.tmn_code')
            ?: env('VNPAY_TMN_CODE');

        $hashSecret = config('vnpay.hash_secret')
            ?: config('services.vnpay.hash_secret')
            ?: env('VNPAY_HASH_SECRET');

        $returnUrl = config('vnpay.return_url')
            ?: config('services.vnpay.return_url')
            ?: env('VNPAY_RETURN_URL', url('/api/payments/vnpay-return'));

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
        $hashSecret = config('services.vnpay.hash_secret')
            ?: env('VNPAY_HASH_SECRET');

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
