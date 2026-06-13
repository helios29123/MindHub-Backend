<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LearningService
{
    /**
     * Get the paginated list of purchased courses for a user.
     *
     * @param User $user
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getPurchasedCourses(User $user, array $params): LengthAwarePaginator
    {
        $perPage = min((int) ($params['per_page'] ?? 10), 100);

        $query = Enrollment::with(['course.instructor.instructorProfile'])
            ->where('user_id', $user->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->whereHas('course', function ($q) {
                $q->whereNull('deleted_at');
            });

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->orderByDesc('id')
            ->paginate($perPage);
    }
}
