<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly CoursePurchaseGuardService $coursePurchaseGuardService
    ) {
    }

    public function createOrder(array $data, int $userId): object
    {
        $courseId = (int) ($data['course_id'] ?? 0);

        if ($courseId <= 0) {
            throw new BusinessException('Thiếu mã khóa học.', 422);
        }

        return DB::transaction(function () use ($courseId, $userId): object {
            $course = $this->coursePurchaseGuardService->assertCanBuyCourse($userId, $courseId);

            $pendingQuery = DB::table('orders')
                ->where('user_id', $userId)
                ->where('course_id', $courseId);

            if (Schema::hasColumn('orders', 'status')) {
                $pendingQuery->where('status', Order::STATUS_PENDING);
            }

            if (Schema::hasColumn('orders', 'payment_status')) {
                $pendingQuery->where('payment_status', Order::PAYMENT_UNPAID);
            }

            if (Schema::hasColumn('orders', 'order_type')) {
                $pendingQuery->where('order_type', Order::TYPE_COURSE_PURCHASE);
            }

            if (Schema::hasColumn('orders', 'deleted_at')) {
                $pendingQuery->whereNull('deleted_at');
            }

            $pendingOrder = $pendingQuery->first();

            if ($pendingOrder) {
                return $pendingOrder;
            }

            $amount = $this->resolveCoursePrice($course);

            $insertData = [
                'user_id' => $userId,
                'course_id' => $courseId,
            ];

            if (Schema::hasColumn('orders', 'coupon_id')) {
                $insertData['coupon_id'] = null;
            }

            if (Schema::hasColumn('orders', 'order_code')) {
                $insertData['order_code'] = $this->generateOrderCode();
            }

            if (Schema::hasColumn('orders', 'order_type')) {
                $insertData['order_type'] = Order::TYPE_COURSE_PURCHASE;
            }

            /*
            |--------------------------------------------------------------------------
            | Money columns
            |--------------------------------------------------------------------------
            | DB hiện tại của bạn không có cột price trong orders.
            | Vì vậy tuyệt đối không insert price nếu cột không tồn tại.
            */
            if (Schema::hasColumn('orders', 'price')) {
                $insertData['price'] = $amount;
            }

            if (Schema::hasColumn('orders', 'price_snapshot')) {
                $insertData['price_snapshot'] = $amount;
            }

            if (Schema::hasColumn('orders', 'amount')) {
                $insertData['amount'] = $amount;
            }

            if (Schema::hasColumn('orders', 'discount_amount')) {
                $insertData['discount_amount'] = 0;
            }

            if (Schema::hasColumn('orders', 'final_amount')) {
                $insertData['final_amount'] = $amount;
            }

            if (Schema::hasColumn('orders', 'status')) {
                $insertData['status'] = Order::STATUS_PENDING;
            }

            if (Schema::hasColumn('orders', 'payment_status')) {
                $insertData['payment_status'] = Order::PAYMENT_UNPAID;
            }

            if (Schema::hasColumn('orders', 'payment_method')) {
                $insertData['payment_method'] = null;
            }

            if (Schema::hasColumn('orders', 'provider_transaction_id')) {
                $insertData['provider_transaction_id'] = null;
            }

            if (Schema::hasColumn('orders', 'paid_at')) {
                $insertData['paid_at'] = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Credit package columns
            |--------------------------------------------------------------------------
            | Với đơn mua khóa học thì các cột gói lượt phải null.
            */
            if (Schema::hasColumn('orders', 'credit_package_id')) {
                $insertData['credit_package_id'] = null;
            }

            if (Schema::hasColumn('orders', 'package_snapshot_name')) {
                $insertData['package_snapshot_name'] = null;
            }

            if (Schema::hasColumn('orders', 'package_snapshot_credits')) {
                $insertData['package_snapshot_credits'] = null;
            }

            if (Schema::hasColumn('orders', 'created_at')) {
                $insertData['created_at'] = now();
            }

            if (Schema::hasColumn('orders', 'updated_at')) {
                $insertData['updated_at'] = now();
            }

            $orderId = DB::table('orders')->insertGetId($insertData);

            return DB::table('orders')->where('id', $orderId)->first();
        });
    }

    public function showUserOrder(int $orderId, int $userId): object
    {
        $query = DB::table('orders')
            ->where('id', $orderId)
            ->where('user_id', $userId);

        if (Schema::hasColumn('orders', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $order = $query->first();

        if (! $order) {
            throw new BusinessException('Không tìm thấy đơn hàng.', 404);
        }

        return $order;
    }

    public function getMyOrders(int $userId, array $filters = []): array
    {
        $query = DB::table('orders')
            ->where('user_id', $userId)
            ->orderByDesc('id');

        if (Schema::hasColumn('orders', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (! empty($filters['status']) && Schema::hasColumn('orders', 'status')) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_status']) && Schema::hasColumn('orders', 'payment_status')) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['order_type']) && Schema::hasColumn('orders', 'order_type')) {
            $query->where('order_type', $filters['order_type']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min($perPage, 100));

        return $query->paginate($perPage)->toArray();
    }

    public function cancelUserOrder(int $orderId, int $userId): object
    {
        return DB::transaction(function () use ($orderId, $userId): object {
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

            if (Schema::hasColumn('orders', 'status') && (string) ($order->status ?? '') !== Order::STATUS_PENDING) {
                throw new BusinessException('Chỉ được hủy đơn hàng đang chờ thanh toán.', 409);
            }

            if (Schema::hasColumn('orders', 'payment_status') && (string) ($order->payment_status ?? '') === Order::PAYMENT_PAID) {
                throw new BusinessException('Không thể hủy đơn hàng đã thanh toán.', 409);
            }

            $updateData = [];

            if (Schema::hasColumn('orders', 'status')) {
                $updateData['status'] = Order::STATUS_CANCELLED;
            }

            if (Schema::hasColumn('orders', 'updated_at')) {
                $updateData['updated_at'] = now();
            }

            DB::table('orders')
                ->where('id', $orderId)
                ->update($updateData);

            return DB::table('orders')->where('id', $orderId)->first();
        });
    }

    private function resolveCoursePrice(object $course): float
    {
        $salePrice = $course->sale_price ?? null;

        if ($salePrice !== null && (float) $salePrice > 0) {
            return (float) $salePrice;
        }

        return (float) ($course->price ?? 0);
    }

    private function generateOrderCode(): string
    {
        return 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
    }
}
