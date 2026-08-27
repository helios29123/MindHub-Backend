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
    public function getCourseReviews(array $filters): LengthAwarePaginator
    {
        return $this->courseModerationRepository->paginateCourseReviews($filters);
    }
    public function approveCourse(int $courseId, int $adminId): Course
    {
        return DB::transaction(function () use ($courseId, $adminId): Course {
            $course = Course::query()
                ->with('instructor')
                ->whereKey($courseId)
                ->lockForUpdate()
                ->first();

            if (! $course) {
                throw new ModelNotFoundException();
            }

            if ($course->status !== 'pending_review') {
                throw new DomainException('Chỉ khóa học pending_review mới được chuyển sang approved.');
            }

            $course->forceFill([
                'status' => 'approved',
                'reviewed_by' => $adminId,
                'admin_reject_reason' => null,
                'published_at' => null,
            ])->save();

            $freshCourse = $course->fresh(['instructor', 'categories']);

            if (! $freshCourse) {
                throw new ModelNotFoundException();
            }

            return $freshCourse;
        });
    }

    public function rejectCourse(int $courseId, string $reason, int $adminId): Course
    {
        return DB::transaction(function () use ($courseId, $reason, $adminId): Course {
            $course = Course::query()
                ->with('instructor')
                ->whereKey($courseId)
                ->lockForUpdate()
                ->first();

            if (! $course) {
                throw new ModelNotFoundException();
            }

            if ($course->status !== 'pending_review') {
                throw new DomainException('Chỉ khóa học pending_review mới được chuyển sang rejected.');
            }

            $course->forceFill([
                'status' => 'rejected',
                'reviewed_by' => $adminId,
                'admin_reject_reason' => trim($reason),
                'published_at' => null,
            ])->save();

            $freshCourse = $course->fresh(['instructor', 'categories']);

            if (! $freshCourse) {
                throw new ModelNotFoundException();
            }

            return $freshCourse;
        });
    }

}