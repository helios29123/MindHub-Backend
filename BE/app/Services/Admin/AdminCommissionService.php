<?php

namespace App\Services\Admin;

use App\Models\CommissionRule;
use App\Models\User;
use App\Repositories\Admin\AdminCommissionRepository;

final class AdminCommissionService
{
    public function __construct(private readonly AdminCommissionRepository $repo, private readonly AdminNotificationService $notifications) {}
    public function all()
    {
        return $this->repo->all();
    }
    public function update(CommissionRule $rule, array $data, User $admin): CommissionRule
    {
        $old = $rule->toArray();
        $rule->update($data);
        $this->notifications->audit($admin, 'commission_rule.update', $rule, $old, $rule->fresh()->toArray());
        return $rule->fresh();
    }
}
