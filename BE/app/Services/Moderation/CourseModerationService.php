<?php
namespace App\Services\Moderation;
use App\Models\Course;
use App\Repositories\Moderation\CourseModerationRepository;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
class CourseModerationService
{
    public function __construct(
        private readonly CourseModerationRepository $courseModerationRepository
    ) {
    }
    public function getPendingCourses(array $filters): LengthAwarePaginator
    {
        return $this->courseModerationRepository->paginatePendingCourses($filters);
    }
    public function rejectCourse(int $courseId, string $reason): Course
    {
        return DB::transaction(function () use ($courseId, $reason): Course {
            $course = Course::query()
                ->with('instructor')
                ->whereKey($courseId)
                ->lockForUpdate()
                ->first();
            if (! $course) {
                throw new ModelNotFoundException();
            }
            if ($course->status !== 'pending_review') {
                throw new DomainException('Trạng thái khóa học không hợp lệ để xử lý.');
            }
            $course->forceFill([
                'status' => 'rejected',
                'admin_reject_reason' => $reason,
            ])->save();
            $freshCourse = $course->fresh(['instructor']);
            if (! $freshCourse) {
                throw new ModelNotFoundException();
            }
            return $freshCourse;
        });
    }
}