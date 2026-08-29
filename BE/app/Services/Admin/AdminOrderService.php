<?php
namespace App\Services\Admin;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminOrderService
{
    public function paginateOrders(array $filters): LengthAwarePaginator
    {
        $perPage = min((int) ($filters['per_page'] ?? 20), 100);
        $query = Order::query()
            ->with([
                'user:id,full_name,email,role,status',
                'course:id,title,slug,status,price,sale_price',
                'coupon:id,code,campaign_type,discount_type,discount_value,max_discount_amount,status',
                'revenue',
                'enrollment',
            ]);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query->where('payment_status', $filters['payment_status']);
        }
if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }
        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }
        if (!empty($filters['order_code'])) {
            $query->where('order_code', 'like', '%' . trim($filters['order_code']) . '%');
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getOrder(int $id): ?Order
    {
        return Order::with([
            'user',
            'course',
            'coupon',
            'revenue',
            'enrollment',
        ])->find($id);
    }

    public function getOrdersSummary(): array
    {
        $total = Order::count();
        $pending = Order::where('status', Order::STATUS_PENDING_PAYMENT)->count();
        $paid = Order::where('status', Order::STATUS_PAID)->count();
        $failed = Order::where('status', Order::STATUS_FAILED)->count();
        $cancelled = Order::where('status', Order::STATUS_CANCELLED)->count();
        $expired = Order::where('status', Order::STATUS_EXPIRED)->count();

        $paidOrdersQuery = Order::where('status', Order::STATUS_PAID);
        
        $totalPaidAmount = (float) $paidOrdersQuery->sum('amount');
        $paidCount = $paidOrdersQuery->count();
        $averageOrderValue = $paidCount > 0 ? $totalPaidAmount / $paidCount : 0.0;
        $successRate = $total > 0 ? ($paidCount / $total) * 100.0 : 0.0;
        $uncompletedCount = $failed + $cancelled + $expired;

        // Calculate anomaly count
        $anomalyCount = 0;
        $allOrders = Order::select('status')->get();
        foreach ($allOrders as $o) {
            $allowed = false;
            if (in_array($o->status, [
                Order::STATUS_PENDING_PAYMENT,
                Order::STATUS_PAID,
                Order::STATUS_FAILED,
                Order::STATUS_CANCELLED,
                Order::STATUS_EXPIRED,
            ], true)) {
                $allowed = true;
            }
            if (!$allowed) {
                $anomalyCount++;
            }
        }

        return [
            'total_orders' => $total,
            'pending_orders' => $pending,
            'paid_orders' => $paid,
            'failed_orders' => $failed,
            'cancelled_orders' => $cancelled,
            'expired_orders' => $expired,
            'total_paid_amount' => $totalPaidAmount,
            'average_order_value' => $averageOrderValue,
            'payment_success_rate' => round($successRate, 1),
            'incomplete_orders' => $uncompletedCount,
            'anomaly_count' => $anomalyCount,
        ];
    }
}