<?php

namespace App\Repositories\Admin;

use App\Models\CommissionRule;

final class AdminCommissionRepository
{
    public function all()
    {
        return CommissionRule::query()->orderBy('id')->get();
    }
}
