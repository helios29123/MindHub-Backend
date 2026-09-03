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
        $freshCourse = DB::transaction(function () use ($courseId, $adminId): Course {
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
                'status' => 'published',
                'reviewed_by' => $adminId,
                'admin_reject_reason' => null,
                'published_at' => now(),
            ])->save();

            $fresh = $course->fresh(['instructor', 'categories']);

            if (! $fresh) {
                throw new ModelNotFoundException();
            }

            return $fresh;
        });

        // Gửi Thông báo web & Email cho Giảng viên
        try {
            $instructor = $freshCourse->instructor;
            if ($instructor) {
                \App\Models\Notification::create([
                    'user_id' => $instructor->id,
                    'type' => 'course_approved',
                    'title' => 'Khóa học của bạn đã được phê duyệt!',
                    'message' => "Chúc mừng! Khóa học \"{$freshCourse->title}\" đã được phê duyệt và xuất bản chính thức trên MindHub.",
                    'action_url' => "/courses/" . ($freshCourse->slug ?: $freshCourse->id),
                    'channel' => 'web',
                ]);

                if (!empty($instructor->email)) {
                    \Illuminate\Support\Facades\Mail::to($instructor->email)->send(
                        new \App\Mail\CourseApprovedNotificationMail($instructor, $freshCourse)
                    );
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send course approval notification/email: ' . $e->getMessage());
        }

        return $freshCourse;
    }

    public function rejectCourse(int $courseId, string $reason, int $adminId): Course
    {
        $freshCourse = DB::transaction(function () use ($courseId, $reason, $adminId): Course {
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

            $fresh = $course->fresh(['instructor', 'categories']);

            if (! $fresh) {
                throw new ModelNotFoundException();
            }

            return $fresh;
        });

        // Gửi Thông báo web & Email cho Giảng viên kèm lý do từ chối
        try {
            $instructor = $freshCourse->instructor;
            if ($instructor) {
                \App\Models\Notification::create([
                    'user_id' => $instructor->id,
                    'type' => 'course_rejected',
                    'title' => 'Khóa học của bạn cần được điều chỉnh',
                    'message' => "Khóa học \"{$freshCourse->title}\" chưa đạt tiêu chuẩn phê duyệt. Lý do: " . trim($reason),
                    'action_url' => "/instructor/courses/{$freshCourse->id}/edit",
                    'channel' => 'web',
                ]);

                if (!empty($instructor->email)) {
                    \Illuminate\Support\Facades\Mail::to($instructor->email)->send(
                        new \App\Mail\CourseRejectedNotificationMail($instructor, $freshCourse, trim($reason))
                    );
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send course rejection notification/email: ' . $e->getMessage());
        }

        return $freshCourse;
    }

}