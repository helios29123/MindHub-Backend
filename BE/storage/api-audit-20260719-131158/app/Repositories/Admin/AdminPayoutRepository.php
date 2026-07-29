<?php

namespace App\Repositories\Admin;

use App\Models\PayoutBatch;
use App\Models\PayoutItem;
use App\Models\Revenue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AdminPayoutRepository
{
    public function paginate(array $filters)
    {
        $q = PayoutBatch::query()->withCount('items')->latest();
        if (!empty($filters['status'])) $q->where('status', $filters['status']);
        if (!empty($filters['period_month'])) $q->where('period_month', $filters['period_month']);
        if (!empty($filters['period_year'])) $q->where('period_year', $filters['period_year']);
        return $q->paginate($filters['per_page'] ?? 15);
    }
    public function availableRevenueByInstructor(int $month, int $year): Collection
    {
        return Revenue::query()->where('status', 'available')->whereMonth('earned_at', $month)->whereYear('earned_at', $year)->select('instructor_id', DB::raw('SUM(gross_amount) gross_amount'), DB::raw('SUM(instructor_amount) instructor_amount'), DB::raw('SUM(platform_fee_amount) platform_fee_amount'))->groupBy('instructor_id')->get();
    }
}
