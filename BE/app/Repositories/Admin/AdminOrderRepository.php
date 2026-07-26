<?php

namespace App\Repositories\Admin;

use App\Models\Order;

final class AdminOrderRepository
{
    public function paginate(array $filters)
    {
        $q = Order::query()->with(['user', 'course', 'coupon', 'revenue'])->latest();
        foreach (['status', 'payment_status', 'payment_method', 'sale_channel'] as $f) {
            if (!empty($filters[$f])) $q->where($f, $filters[$f]);
        }
        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $q->where(function ($w) use ($search) {
                $w->where('order_code', 'like', "%$search%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%"))->orWhereHas('course', fn($c) => $c->where('title', 'like', "%$search%"));
            });
        }
        if (!empty($filters['date_from'])) $q->whereDate('created_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $q->whereDate('created_at', '<=', $filters['date_to']);
        return $q->paginate($filters['per_page'] ?? 15);
    }
}
