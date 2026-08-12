<?php
namespace App\Repositories\Marketing;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class MarketingCouponRepository
{
    public function paginateForInstructor(int $instructorId, array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 100);
        $query = Coupon::query()
            ->with('course:id,title,instructor_id,status')
            ->where('user_id', $instructorId)
            ->whereHas('course', function ($courseQuery) use ($instructorId): void {
                $courseQuery->where('instructor_id', $instructorId);
            });

        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', $search)
                  ->orWhere('name', 'like', $search);
            });
        }

        $now = now();

        if (!empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'active') {
                $query->where('status', 'active')
                    ->where(function ($q) use ($now) {
                        $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
                    })
                    ->where(function ($q) {
                        $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
                    });
            } elseif ($status === 'inactive') {
                $query->where('status', 'inactive');
            } elseif ($status === 'expired') {
                $query->where('status', 'active')
                    ->whereNotNull('end_at')
                    ->where('end_at', '<', $now);
            } elseif ($status === 'used_up') {
                $query->where('status', 'active')
                    ->whereNotNull('usage_limit')
                    ->whereRaw('used_count >= usage_limit');
            } else {
                $query->where('status', $status);
            }
        }

        return $query
            ->orderByDesc('id')
            ->paginate($perPage);
    }
    public function findById(int $id): ?Coupon
    {
        return Coupon::query()
            ->with('course:id,title,instructor_id,status')
            ->find($id);
    }
    public function findCourseById(int $courseId): ?Course
    {
        return Course::query()->find($courseId);
    }
    public function create(array $data): Coupon
    {
        return Coupon::query()->create($data)->load('course:id,title,instructor_id,status');
    }
    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->update($data);
        return $coupon->refresh()->load('course:id,title,instructor_id,status');
    }
    public function delete(Coupon $coupon): Coupon
    {
        $coupon->delete();
        return $coupon;
    }
}