<?php

namespace App\Services\Learning;

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

    /**
     * Save the learner's progress for a video lesson.
     *
     * @param User $user
     * @param int $lessonId
     * @param array $data
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function saveVideoProgress(User $user, int $lessonId, array $data): array
    {
        $lesson = \App\Models\Lesson::find($lessonId);

        if (!$lesson) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        if ($lesson->lesson_type !== 'video') {
            throw new \App\Exceptions\BusinessException('Bài học không phải dạng video.', 422);
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

        $currentSecond = (int) $data['current_second'];
        $durationSecond = isset($data['duration_second']) ? (int) $data['duration_second'] : null;
        $isCompletedInput = !empty($data['is_completed']);

        // Validate current_second
        if ($lesson->video_duration_seconds !== null && $currentSecond > $lesson->video_duration_seconds) {
            throw new \App\Exceptions\BusinessException('Tiến độ video không hợp lệ.', 422);
        }

        if ($durationSecond !== null && $currentSecond > $durationSecond) {
            throw new \App\Exceptions\BusinessException('Tiến độ video không hợp lệ.', 422);
        }

        // Upsert video progress
        \App\Models\VideoProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lessonId,
            ],
            [
                'current_second' => $currentSecond,
            ]
        );

        // Determine if lesson is completed
        $isCompleted = $isCompletedInput
            || ($lesson->video_duration_seconds !== null && $currentSecond >= $lesson->video_duration_seconds)
            || ($durationSecond !== null && $currentSecond >= $durationSecond);

        // Get/Create lesson progress
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

        $updates = [
            'last_accessed_at' => now(),
        ];

        if ($isCompleted) {
            $updates['status'] = 'completed';
            if (!$progress->completed_at) {
                $updates['completed_at'] = now();
            }
        } else {
            if ($progress->status !== 'completed') {
                $updates['status'] = 'in_progress';
                if (!$progress->started_at) {
                    $updates['started_at'] = now();
                }
            }
        }

        $progress->update($updates);

        return [
            'course' => $course,
            'lesson' => $lesson,
            'progress' => $progress,
            'current_second' => $currentSecond,
        ];
    }

    /**
     * Resume learning the most recently accessed lesson or the first lesson of the latest course.
     *
     * @param User $user
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function resumeLearning(User $user): array
    {
        $hasEnrollment = Enrollment::where('user_id', $user->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->exists();

        if (!$hasEnrollment) {
            throw new \App\Exceptions\BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        // Find the most recently accessed lesson progress for a course the user is enrolled in
        $latestProgress = \App\Models\LessonProgress::where('user_id', $user->id)
            ->whereHas('lesson', function ($query) use ($user) {
                $query->where('status', 'published')
                    ->whereHas('course', function ($q) use ($user) {
                        $q->where('status', 'published')
                            ->whereNull('deleted_at')
                            ->whereHas('enrollments', function ($eq) use ($user) {
                                $eq->where('user_id', $user->id)
                                    ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED]);
                            });
                    });
            })
            ->orderByDesc('last_accessed_at')
            ->first();

        if ($latestProgress) {
            $lesson = $latestProgress->lesson;
            $course = $lesson->course;

            $currentSecond = 0;
            if ($lesson->lesson_type === 'video') {
                $videoProgress = \App\Models\VideoProgress::where('user_id', $user->id)
                    ->where('lesson_id', $lesson->id)
                    ->first();
                if ($videoProgress) {
                    $currentSecond = (int) $videoProgress->current_second;
                }
            }

            return [
                'course' => $course,
                'lesson' => $lesson,
                'progress' => $latestProgress,
                'current_second' => $currentSecond,
            ];
        }

        // If no progress, find the latest enrolled course
        $latestEnrollment = Enrollment::where('user_id', $user->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->whereHas('course', function ($q) {
                $q->where('status', 'published')->whereNull('deleted_at');
            })
            ->orderByDesc('enrolled_at')
            ->orderByDesc('id')
            ->first();

        if (!$latestEnrollment) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $course = $latestEnrollment->course;

        // Find the first published lesson in the course (ordered by section and lesson sort_order)
        $firstSection = $course->sections()
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->first();

        if (!$firstSection) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $firstLesson = $firstSection->lessons()
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->first();

        if (!$firstLesson) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        return [
            'course' => $course,
            'lesson' => $firstLesson,
            'progress' => null,
            'current_second' => 0,
        ];
    }

    /**
     * Mark a lesson as completed or in_progress and update course enrollment status accordingly.
     *
     * @param User $user
     * @param int $lessonId
     * @param array $data
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function completeLesson(User $user, int $lessonId, array $data): array
    {
        $lesson = \App\Models\Lesson::find($lessonId);

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

        $completed = (bool) $data['completed'];

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

        $updates = [
            'last_accessed_at' => now(),
        ];

        if ($completed) {
            $updates['status'] = 'completed';
            if (!$progress->completed_at) {
                $updates['completed_at'] = now();
            }
            if (!$progress->started_at) {
                $updates['started_at'] = now();
            }
            if ($progress->learning_duration_seconds == 0 && $lesson->video_duration_seconds !== null) {
                $updates['learning_duration_seconds'] = $lesson->video_duration_seconds;
            }
        } else {
            if ($progress->status === 'completed') {
                $updates['status'] = 'in_progress';
                $updates['completed_at'] = null;
            }
        }

        $progress->update($updates);

        // Fetch current second from video progress if any
        $currentSecond = 0;
        if ($lesson->lesson_type === 'video') {
            $videoProgress = \App\Models\VideoProgress::where('user_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->first();
            if ($videoProgress) {
                $currentSecond = (int) $videoProgress->current_second;
            }
        }

        // Calculate course completion
        $publishedLessonIds = \App\Models\Lesson::where('course_id', $course->id)
            ->where('status', 'published')
            ->whereHas('section', function ($q) {
                $q->where('status', 'published');
            })
            ->pluck('id');

        $completedLessonsCount = \App\Models\LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $publishedLessonIds)
            ->where('status', 'completed')
            ->count();

        if ($publishedLessonIds->isNotEmpty() && $completedLessonsCount === $publishedLessonIds->count()) {
            $enrollment->update([
                'status' => Enrollment::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        } else {
            if ($enrollment->status === Enrollment::STATUS_COMPLETED) {
                $enrollment->update([
                    'status' => Enrollment::STATUS_ACTIVE,
                    'completed_at' => null,
                ]);
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
     * Get the progress details (total, completed, percent) of a course for a user.
     *
     * @param User $user
     * @param int $courseId
     * @return array
     * @throws \App\Exceptions\BusinessException
     */
    public function getCourseProgress(User $user, int $courseId): array
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

        $publishedLessonIds = \App\Models\Lesson::where('course_id', $courseId)
            ->where('status', 'published')
            ->whereHas('section', function ($q) {
                $q->where('status', 'published');
            })
            ->pluck('id');

        $totalLessons = $publishedLessonIds->count();

        $completedLessons = \App\Models\LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $publishedLessonIds)
            ->where('status', 'completed')
            ->count();

        $progressPercent = 0.00;
        if ($totalLessons > 0) {
            $progressPercent = round(($completedLessons / $totalLessons) * 100, 2);
        }

        // Keep enrollment completion status synchronized
        if ($totalLessons > 0 && $completedLessons === $totalLessons) {
            $enrollment->update([
                'status' => Enrollment::STATUS_COMPLETED,
                'completed_at' => $enrollment->completed_at ?? now(),
            ]);
        } else {
            if ($enrollment->status === Enrollment::STATUS_COMPLETED) {
                $enrollment->update([
                    'status' => Enrollment::STATUS_ACTIVE,
                    'completed_at' => null,
                ]);
            }
        }

        // Update enrollment progress_percent cache column in DB
        $enrollment->update([
            'progress_percent' => $progressPercent,
        ]);

        return [
            'course_id' => $courseId,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percent' => (float) $progressPercent,
        ];
    }

    /**
     * Get the paginated learning logs (timeline) for the authenticated learner.
     *
     * @param User $user
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getLearningLogs(User $user, array $params): LengthAwarePaginator
    {
        $perPage = min((int) ($params['per_page'] ?? 10), 100);

        // Get only course IDs where the user has active or completed enrollments
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->pluck('course_id');

        $query = \App\Models\LessonProgress::with(['lesson.course'])
            ->where('user_id', $user->id)
            ->whereHas('lesson', function ($q) use ($enrolledCourseIds) {
                $q->where('status', 'published')
                  ->whereIn('course_id', $enrolledCourseIds)
                  ->whereHas('course', function ($qc) {
                      $qc->where('status', 'published')
                         ->whereNull('deleted_at');
                  });
            });

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $paginatedLogs = $query->orderByDesc('last_accessed_at')
            ->paginate($perPage);

        // Map video progress current_second for video lessons
        $lessonIds = $paginatedLogs->pluck('lesson_id')->unique();
        $videoProgresses = \App\Models\VideoProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');

        $paginatedLogs->getCollection()->transform(function ($progress) use ($videoProgresses) {
            $vp = $videoProgresses->get($progress->lesson_id);
            $progress->current_second = $vp ? (int) $vp->current_second : 0;
            return $progress;
        });

        return $paginatedLogs;
    }

    /**
     * Get details of a lesson asset for download.
     *
     * @param User $user
     * @param int $assetId
     * @return \App\Models\LessonAsset
     * @throws \App\Exceptions\BusinessException
     */
    public function downloadAsset(User $user, int $assetId): \App\Models\LessonAsset
    {
        $asset = \App\Models\LessonAsset::find($assetId);

        if (!$asset) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $lesson = $asset->lesson;
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

        return $asset;
    }
}
