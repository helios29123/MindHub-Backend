<?php

namespace App\Repositories\Admin;

use App\Models\PayoutAccount;

final class AdminPayoutAccountRepository
{
    public function paginate(array $filters)
    {
        $q = PayoutAccount::query()->with('user')->latest();
        if (!empty($filters['status'])) $q->where('status', $filters['status']);
        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%"));
        }
        return $q->paginate($filters['per_page'] ?? 15);
    }
}
