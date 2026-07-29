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
    private const APPROVED_STATUS = 'approved';
    public function __construct(
        private readonly CourseModerationRepository $courseModerationRepository
    ) {
    }
    public function getPendingCourses(array $filters): LengthAwarePaginator
    {
        return $this->courseModerationRepository->paginatePendingCourses($filters);
    }
    public function approveCourse(int $courseId): Course
    {
        return DB::transaction(function () use ($courseId): Course {
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
            $approvedStatus = self::APPROVED_STATUS;
            $course->forceFill([
                'status' => $approvedStatus,
                'admin_reject_reason' => null,
                'published_at' => $approvedStatus === 'published' ? now() : null,
            ])->save();
            $freshCourse = $course->fresh(['instructor']);
            if (! $freshCourse) {
                throw new ModelNotFoundException();
            }
            return $freshCourse;
        });
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