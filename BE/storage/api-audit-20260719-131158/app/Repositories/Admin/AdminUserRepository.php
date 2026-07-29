<?php

namespace App\Repositories\Admin;

use App\Models\User;

final class AdminUserRepository
{
    public function paginate(array $filters)
    {
        $q = User::query()->latest();
        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $q->where(fn($w) => $w->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%"));
        }
        if (!empty($filters['role'])) $q->where('role', $filters['role']);
        if (!empty($filters['status'])) $q->where('status', $filters['status']);
        return $q->paginate($filters['per_page'] ?? 15);
    }
}
