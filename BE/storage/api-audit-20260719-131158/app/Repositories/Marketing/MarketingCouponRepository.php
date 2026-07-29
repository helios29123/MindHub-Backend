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
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['code'])) {
            $query->where('code', 'like', '%' . $filters['code'] . '%');
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