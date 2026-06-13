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
                'coupon:id,code,name,discount_type,discount_value,status',
            ]);
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['payment_status'])) {
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
}