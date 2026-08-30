<?php

namespace App\Repositories\Learning;

use App\Models\Order;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DynamicAlertRepository
{
    public function getPendingOrders(int $learnerId): Collection
    {
        return Order::where('user_id', $learnerId)
            ->whereIn('status', ['pending'])
            ->whereIn('payment_status', ['pending', 'unpaid'])
            ->orderBy('created_at', 'desc')
            ->get();
    }


    public function getInactiveEnrollments(int $learnerId, int $daysInactive = 14): Collection
    {
        $threshold = Carbon::now()->subDays($daysInactive);

        // Fetch enrollments that are active but haven't been accessed recently
        return Enrollment::where('user_id', $learnerId)
            ->where('status', 'active')
            ->where(function ($query) use ($threshold) {
                $query->where('last_accessed_at', '<', $threshold)
                      ->orWhereNull('last_accessed_at');
            })
            ->get();
    }
}
