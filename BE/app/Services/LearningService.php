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

    /**
     * Get details of a lesson for the enrolled user and record progress.
     *
     * @param User $user
     * @param int $lessonId
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function getLessonDetails(User $user, int $lessonId): array
    {
        $lesson = \App\Models\Lesson::with('assets')->find($lessonId);

        if (!$lesson) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $course = $lesson->course;
        if (!$course) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        if ($lesson->status !== 'published' || $course->status !== 'published') {
            throw new \App\Exceptions\BusinessException('Nội dung chưa khả dụng.', 403);
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->first();

        if (!$enrollment) {
            throw new \App\Exceptions\BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        // Upsert lesson progress
        $progress = \App\Models\LessonProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lessonId,
            ],
            [
                'status' => 'in_progress',
                'started_at' => now(),
                'last_accessed_at' => now(),
                'learning_duration_seconds' => 0,
            ]
        );

        if (!$progress->wasRecentlyCreated) {
            $updates = ['last_accessed_at' => now()];
            if ($progress->status === 'not_started') {
                $updates['status'] = 'in_progress';
                $updates['started_at'] = now();
            }
            $progress->update($updates);
        }

        // Get video progress if lesson type is video
        $currentSecond = 0;
        if ($lesson->lesson_type === 'video') {
            $videoProgress = \App\Models\VideoProgress::where('user_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->first();
            if ($videoProgress) {
                $currentSecond = (int) $videoProgress->current_second;
            }
        }

        return [
            'course' => $course,
            'lesson' => $lesson,
            'progress' => $progress,
            'current_second' => $currentSecond,
        ];
    }

    /**
     * Get the outline (sections & lessons) of a purchased course along with the user's progress.
     *
     * @param User $user
     * @param int $courseId
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function getCourseOutline(User $user, int $courseId): array
    {
        $course = \App\Models\Course::find($courseId);

        if (!$course) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        if ($course->status !== 'published') {
            throw new \App\Exceptions\BusinessException('Nội dung chưa khả dụng.', 403);
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->first();

        if (!$enrollment) {
            throw new \App\Exceptions\BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        $course->load([
            'sections' => function ($query) {
                $query->where('status', 'published')->orderBy('sort_order');
            },
            'sections.lessons' => function ($query) {
                $query->where('status', 'published')->orderBy('sort_order');
            }
        ]);

        $lessonIds = $course->sections->flatMap->lessons->pluck('id');

        $progresses = \App\Models\LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');

        return [
            'sections' => $course->sections,
            'progresses' => $progresses,
        ];
    }
}
