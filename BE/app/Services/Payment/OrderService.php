<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Course;
use App\Models\Order;
use App\Repositories\Payment\EnrollmentRepository;
use App\Repositories\Payment\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EnrollmentRepository $enrollmentRepository
    ) {
    }

    public function createOrder(array $orderData, int $userId): Order
    {
        return DB::transaction(function () use ($orderData, $userId) {
            $course = Course::where('id', $orderData['course_id'])->first();

            if (! $course) {
                throw new BusinessException('Không tìm thấy khóa học.', 404);
            }

            if ($course->status !== 'published') {
                throw new BusinessException('Khóa học chưa mở bán.', 403);
            }

            if ($this->enrollmentRepository->findByUserAndCourse($userId, $course->id)) {
                throw new BusinessException('Bạn đã sở hữu khóa học này.', 409);
            }

            if ($this->orderRepository->existsActiveOrder($userId, $course->id)) {
                throw new BusinessException('Bạn đã có đơn hàng cho khóa học này.', 409);
            }

            $priceSnapshot = $course->sale_price ?? $course->price;

            return $this->orderRepository->create([
                'user_id' => $userId,
                'course_id' => $course->id,
                'order_code' => $this->generateOrderCode(),
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'price_snapshot' => $priceSnapshot,
                'amount' => $priceSnapshot,
            ]);
        });
    }

    public function showUserOrder(int $orderId, int $userId): Order
    {
        $order = $this->orderRepository->findUserOrder($orderId, $userId);

        if (! $order) {
            throw new BusinessException('Không tìm thấy đơn hàng.', 404);
        }

        return $order;
    }

    public function getMyOrders(array $filters, int $userId): LengthAwarePaginator
    {
        return $this->orderRepository->paginateUserOrders($userId, $filters);
    }

    private function generateOrderCode(): string
    {
        do {
            $orderCode = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
        } while (Order::where('order_code', $orderCode)->exists());

        return $orderCode;
    }
}