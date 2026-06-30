<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentService
{
    public const PLATFORM_FEE_PERCENT = 30;

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
                ?? $data['vnp_TxnRef']
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

    public function createVnpayPayment(array $data, ?int $userId = null): array
    {
        $userId = $userId ?: (int) Auth::id();

        $orderId = (int) ($data['order_id'] ?? 0);

        if ($orderId <= 0) {
            throw new BusinessException('Thiếu mã đơn hàng.', 422);
        }

        return DB::transaction(function () use ($orderId, $userId): array {
            $order = $this->findUserOrderForUpdate($orderId, $userId);

            $this->assertOrderCanCreateVnpayPayment($order);

            $user = DB::table('users')
                ->where('id', $userId)
                ->first();

            if (! $user) {
                throw new BusinessException('Không tìm thấy người dùng.', 404);
            }

            $orderType = $this->resolveOrderType($order);
            $role = (string) ($user->role ?? '');

            if ($role === 'instructor' && $orderType !== Order::TYPE_INSTRUCTOR_CREDIT) {
                throw new BusinessException('Giảng viên chỉ được thanh toán đơn mua lượt tạo khóa học.', 403);
            }

            if (in_array($role, ['learner', 'member'], true) && $orderType !== Order::TYPE_COURSE_PURCHASE) {
                throw new BusinessException('Học viên chỉ được thanh toán đơn mua khóa học.', 403);
            }

            $amount = $this->getOrderAmount($order);

            if ($amount <= 0) {
                throw new BusinessException('Số tiền thanh toán không hợp lệ.', 422);
            }

            $txnRef = $this->generateVnpayTxnRef($order);

            /*
            |--------------------------------------------------------------------------
            | Không update payment_status = processing
            |--------------------------------------------------------------------------
            | DB hiện tại đang nhận unpaid/paid/failed.
            | Vì vậy tạo link VNPAY chỉ lưu payment_method và provider_transaction_id.
            | payment_status vẫn giữ unpaid cho đến khi VNPAY return/callback thành công.
            */
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
                'order_type' => $orderType,
                'amount' => $amount,
                'payment_method' => 'vnpay',
                'provider_transaction_id' => $txnRef,
                'payment_url' => $paymentUrl,
            ];
        });
    }

    public function vnpayReturn(array $params): array
    {
        return $this->handleVnpayReturn($params);
    }

    public function handleVnpayReturn(array $params): array
    {
        $isValid = $this->verifyVnpaySignature($params);

        if (! $isValid) {
            throw new BusinessException('Chữ ký VNPAY không hợp lệ.', 400);
        }

        $responseCode = (string) ($params['vnp_ResponseCode'] ?? '');
        $transactionStatus = (string) ($params['vnp_TransactionStatus'] ?? '');
        $txnRef = (string) ($params['vnp_TxnRef'] ?? '');

        if ($txnRef === '') {
            throw new BusinessException('Thiếu mã giao dịch VNPAY.', 422);
        }

        return DB::transaction(function () use ($responseCode, $transactionStatus, $txnRef, $params): array {
            $order = DB::table('orders')
                ->where('provider_transaction_id', $txnRef)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                throw new BusinessException('Không tìm thấy đơn hàng.', 404);
            }

            if ($responseCode === '00' && $transactionStatus === '00') {
                if (($order->payment_status ?? null) !== Order::PAYMENT_PAID) {
                    $this->markOrderAsPaid($order, 'vnpay', $txnRef);

                    $paidOrder = DB::table('orders')
                        ->where('id', $order->id)
                        ->first();

                    $this->applyPaidSideEffects($paidOrder);
                }

                return [
                    'success' => true,
                    'message' => 'Thanh toán VNPAY thành công.',
                    'order_id' => (int) $order->id,
                    'vnpay' => $params,
                ];
            }

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'status' => Order::STATUS_FAILED,
                    'payment_status' => Order::PAYMENT_FAILED,
                    'updated_at' => now(),
                ]);

            return [
                'success' => false,
                'message' => 'Thanh toán VNPAY thất bại.',
                'order_id' => (int) $order->id,
                'vnpay' => $params,
            ];
        });
    }

    public function webhook(array $payload): array
    {
        return $this->handleVnpayReturn($payload);
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

    private function assertOrderCanCreateVnpayPayment(object $order): void
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

        /*
        |--------------------------------------------------------------------------
        | Chỉ chặn processing nếu DB thật sự đang lưu processing.
        | Không chặn unpaid.
        |--------------------------------------------------------------------------
        */
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
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('enrollments', 'status')) {
            $insertData['status'] = 'active';
        }

        if (Schema::hasColumn('enrollments', 'progress_percent')) {
            $insertData['progress_percent'] = 0;
        }

        DB::table('enrollments')->insert($insertData);
    }

    private function createRevenueAfterCourseOrderPaid(object $order): void
    {
        if (! Schema::hasTable('revenues')) {
            return;
        }

        if (empty($order->course_id)) {
            return;
        }

        $course = DB::table('courses')
            ->where('id', $order->course_id)
            ->first();

        if (! $course) {
            return;
        }

        if (Schema::hasColumn('revenues', 'order_id')) {
            $exists = DB::table('revenues')
                ->where('order_id', $order->id)
                ->exists();

            if ($exists) {
                return;
            }
        }

        $amount = $this->getOrderAmount($order);

        $insertData = [
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('revenues', 'order_id')) {
            $insertData['order_id'] = $order->id;
        }

        if (Schema::hasColumn('revenues', 'course_id')) {
            $insertData['course_id'] = $order->course_id;
        }

        if (Schema::hasColumn('revenues', 'instructor_id')) {
            $insertData['instructor_id'] = $course->instructor_id;
        }

        /*
        |--------------------------------------------------------------------------
        | Rule mới:
        | Course sale revenue thuộc 100% instructor.
        | Platform fee = 0.
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('revenues', 'gross_amount')) {
            $insertData['gross_amount'] = $amount;
        }

        if (Schema::hasColumn('revenues', 'amount')) {
            $insertData['amount'] = $amount;
        }

        if (Schema::hasColumn('revenues', 'platform_fee_percent')) {
            $insertData['platform_fee_percent'] = 0;
        }

        if (Schema::hasColumn('revenues', 'platform_fee_amount')) {
            $insertData['platform_fee_amount'] = 0;
        }

        if (Schema::hasColumn('revenues', 'platform_revenue')) {
            $insertData['platform_revenue'] = 0;
        }

        if (Schema::hasColumn('revenues', 'instructor_amount')) {
            $insertData['instructor_amount'] = $amount;
        }

        if (Schema::hasColumn('revenues', 'instructor_revenue')) {
            $insertData['instructor_revenue'] = $amount;
        }

        DB::table('revenues')->insert($insertData);
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
        return 'VNPAY-' . $order->id . '-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
    }

    private function buildVnpayPaymentUrl(object $order, string $txnRef, float $amount): string
    {
        $vnpUrl = config('services.vnpay.url')
            ?: env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');

        $tmnCode = config('services.vnpay.tmn_code')
            ?: env('VNPAY_TMN_CODE');

        $hashSecret = config('services.vnpay.hash_secret')
            ?: env('VNPAY_HASH_SECRET');

        $returnUrl = config('services.vnpay.return_url')
            ?: env('VNPAY_RETURN_URL', url('/api/payments/vnpay-return'));

        if (empty($tmnCode) || empty($hashSecret)) {
            throw new BusinessException('Chưa cấu hình VNPAY_TMN_CODE hoặc VNPAY_HASH_SECRET.', 500);
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
            'vnp_IpAddr' => request()->ip() ?: '127.0.0.1',
            'vnp_CreateDate' => now()->format('YmdHis'),
        ];

        ksort($vnpParams);

        /*
        |--------------------------------------------------------------------------
        | VNPAY secure hash
        |--------------------------------------------------------------------------
        | Không dùng urldecode(http_build_query()).
        | Build hash data bằng urlencode từng key/value để tránh lỗi sai chữ ký.
        */
        $hashData = $this->buildVnpayHashData($vnpParams);
        $secureHash = hash_hmac('sha512', $hashData, $hashSecret);

        $query = http_build_query($vnpParams);

        return $vnpUrl . '?' . $query . '&vnp_SecureHash=' . $secureHash;
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
