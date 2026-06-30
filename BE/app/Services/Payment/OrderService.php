<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Repositories\Payment\EnrollmentRepository;
use App\Repositories\Payment\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly CoursePurchaseGuardService $coursePurchaseGuardService
    ) {
    }

    public function createOrder(array $data, int $userId): object
    {
        return DB::transaction(function () use ($data, $userId): object {
            $courseId = (int) $data['course_id'];

            $course = $this->coursePurchaseGuardService->assertCanBuyCourse($userId, $courseId);

            $regularPrice = (float) ($course->price ?? 0);
            $salePrice = $course->sale_price ?? null;

            $price = ($salePrice !== null && (float) $salePrice > 0)
                ? (float) $salePrice
                : $regularPrice;

            $insertData = [
                'order_type' => Order::TYPE_COURSE_PURCHASE,
                'coupon_id' => null,
                'course_id' => $courseId,
                'user_id' => $userId,
                'order_code' => $this->generateOrderCode(),
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'payment_method' => null,
                'provider_transaction_id' => null,
                'paid_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('orders', 'price')) {
                $insertData['price'] = $price;
            }

            if (Schema::hasColumn('orders', 'price_snapshot')) {
                $insertData['price_snapshot'] = $price;
            }

            if (Schema::hasColumn('orders', 'amount')) {
                $insertData['amount'] = $price;
            }

            if (Schema::hasColumn('orders', 'final_amount')) {
                $insertData['final_amount'] = $price;
            }

            if (Schema::hasColumn('orders', 'discount_amount')) {
                $insertData['discount_amount'] = 0;
            }

            return $this->orderRepository->create($insertData);
        });
    }

    public function showUserOrder(int $orderId, int $userId): object
    {
        $order = DB::table('orders')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();

        if (! $order) {
            throw new BusinessException('Không tìm thấy đơn hàng.', 404);
        }

        return $order;
    }

    public function cancelUserOrder(int $orderId, int $userId): object
    {
        return DB::transaction(function () use ($orderId, $userId): object {
            $order = DB::table('orders')
                ->where('id', $orderId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                throw new BusinessException('Không tìm thấy đơn hàng.', 404);
            }

            if (! $this->canCancelOrder($order)) {
                throw new BusinessException('Đơn hàng không thể hủy.', 400);
            }

            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'status' => Order::STATUS_CANCELLED,
                    'updated_at' => now(),
                ]);

            return DB::table('orders')
                ->where('id', $orderId)
                ->first();
        });
    }

    public function getMyOrders(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = DB::table('orders')
            ->where('orders.user_id', $userId)
            ->leftJoin('courses', 'courses.id', '=', 'orders.course_id')
            ->select([
                'orders.*',
                'courses.title as course_title',
                'courses.slug as course_slug',
                'courses.thumbnail_url as course_thumbnail_url',
            ])
            ->orderByDesc('orders.id');

        if (! empty($filters['status'])) {
            $query->where('orders.status', $filters['status']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('orders.payment_status', $filters['payment_status']);
        }

        if (! empty($filters['order_type']) && Schema::hasColumn('orders', 'order_type')) {
            $query->where('orders.order_type', $filters['order_type']);
        }

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        return $query->paginate($perPage);
    }

    private function canCancelOrder(object $order): bool
    {
        $status = (string) ($order->status ?? '');
        $paymentStatus = (string) ($order->payment_status ?? '');

        if ($status !== Order::STATUS_PENDING) {
            return false;
        }

        if ($paymentStatus === Order::PAYMENT_PAID) {
            return false;
        }

        return true;
    }

    private function generateOrderCode(): string
    {
        return 'ORD-' . now()->format('YmdHis') . random_int(1000, 9999);
    }
}
