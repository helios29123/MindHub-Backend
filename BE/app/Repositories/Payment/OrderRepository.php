<?php

namespace App\Repositories\Payment;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function create(array $orderData): Order
    {
        return Order::create($orderData);
    }

    public function findUserOrder(int $orderId, int $userId): ?Order
    {
        return Order::with(['course', 'coupon', 'enrollment'])
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findUserOrderForUpdate(int $orderId, int $userId): ?Order
    {
        return Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();
    }

    public function findForUpdate(int $orderId): ?Order
    {
        return Order::where('id', $orderId)
            ->lockForUpdate()
            ->first();
    }

    public function existsActiveOrder(int $userId, int $courseId): bool
    {
        return Order::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_PAID,
            ])
            ->whereIn('payment_status', [
                Order::PAYMENT_UNPAID,
                Order::PAYMENT_PROCESSING,
                Order::PAYMENT_PAID,
            ])
            ->exists();
    }

    public function existsTransactionForAnotherOrder(string $transactionCode, int $orderId): bool
    {
        return Order::where('provider_transaction_id', $transactionCode)
            ->where('id', '!=', $orderId)
            ->exists();
    }

    public function paginateUserOrders(int $userId, array $filters): LengthAwarePaginator
    {
        $query = Order::with(['course', 'coupon'])
            ->where('user_id', $userId)
            ->latest();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }
}
