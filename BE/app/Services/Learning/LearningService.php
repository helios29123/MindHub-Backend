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

        $paginator = $query->orderByDesc('id')
            ->paginate($perPage);

        foreach ($paginator->items() as $enrollment) {
            $courseId = $enrollment->course_id;
            $publishedLessonIds = \App\Models\Lesson::where('course_id', $courseId)
                ->where('status', 'published')
                ->whereHas('section', function ($q) {
                    $q->where('status', 'published');
                })
                ->pluck('id');

            if ($publishedLessonIds->isNotEmpty()) {
                $completedCount = \App\Models\LessonProgress::where('user_id', $user->id)
                    ->whereIn('lesson_id', $publishedLessonIds)
                    ->where('status', 'completed')
                    ->count();

                $realProgress = round(($completedCount / $publishedLessonIds->count()) * 100, 2);
                if (abs((float) $enrollment->progress_percent - $realProgress) > 0.01) {
                    $enrollment->progress_percent = $realProgress;
                    if ($realProgress >= 100) {
                        $enrollment->status = Enrollment::STATUS_COMPLETED;
                        $enrollment->completed_at = $enrollment->completed_at ?? now();
                    } else if ($enrollment->status === Enrollment::STATUS_COMPLETED) {
                        $enrollment->status = Enrollment::STATUS_ACTIVE;
                        $enrollment->completed_at = null;
                    }
                    $enrollment->save();
                }
            }
        }

        return $paginator;
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

        // Sync enrollment progress_percent and completion status
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

        $progressPercent = $publishedLessonIds->count() > 0 ? round(($completedLessonsCount / $publishedLessonIds->count()) * 100, 2) : 0.00;
        $isAllCompleted = $publishedLessonIds->isNotEmpty() && $completedLessonsCount >= $publishedLessonIds->count();

        $enrollment->update([
            'progress_percent' => $progressPercent,
            'status' => $isAllCompleted ? Enrollment::STATUS_COMPLETED : Enrollment::STATUS_ACTIVE,
            'completed_at' => $isAllCompleted ? ($enrollment->completed_at ?? now()) : null,
            'last_accessed_at' => now(),
        ]);

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

        $progressPercent = $publishedLessonIds->count() > 0 ? round(($completedLessonsCount / $publishedLessonIds->count()) * 100, 2) : 0.00;
        $isAllCompleted = $publishedLessonIds->isNotEmpty() && $completedLessonsCount >= $publishedLessonIds->count();

        $enrollment->update([
            'progress_percent' => $progressPercent,
            'status' => $isAllCompleted ? Enrollment::STATUS_COMPLETED : Enrollment::STATUS_ACTIVE,
            'completed_at' => $isAllCompleted ? ($enrollment->completed_at ?? now()) : null,
            'last_accessed_at' => now(),
        ]);

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

    /**
     * Suggest the next lesson in the course structure.
     *
     * @param User $user
     * @param int $lessonId
     * @return \App\Models\Lesson|null
     * @throws \App\Exceptions\BusinessException
     */
    public function nextLesson(User $user, int $lessonId): ?\App\Models\Lesson
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

        $section = $lesson->section;
        if (!$section || $section->status !== 'published') {
            throw new \App\Exceptions\BusinessException('Nội dung chưa khả dụng.', 403);
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->first();

        if (!$enrollment) {
            throw new \App\Exceptions\BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        // Query all published lessons in the course, ordered sequentially
        $lessons = \App\Models\Lesson::where('lessons.course_id', $course->id)
            ->where('lessons.status', 'published')
            ->whereHas('section', function ($q) {
                $q->where('status', 'published');
            })
            ->join('course_sections', 'lessons.course_section_id', '=', 'course_sections.id')
            ->orderBy('course_sections.sort_order', 'asc')
            ->orderBy('course_sections.id', 'asc')
            ->orderBy('lessons.sort_order', 'asc')
            ->orderBy('lessons.id', 'asc')
            ->select('lessons.*')
            ->get();

        $currentIndex = $lessons->search(fn($l) => $l->id === $lessonId);

        if ($currentIndex !== false && $currentIndex < $lessons->count() - 1) {
            return $lessons[$currentIndex + 1];
        }

        return null;
    }

    public function getLessonNotes(int $lessonId, User $user)
    {
        return \App\Models\LessonNote::where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->orderBy('note_time_second', 'asc')
            ->get();
    }

    public function createLessonNote(int $lessonId, array $data, User $user): \App\Models\LessonNote
    {
        $lesson = \App\Models\Lesson::find($lessonId);
        if (!$lesson) {
            throw new BusinessException('Không tìm thấy bài học.', 404);
        }

        return \App\Models\LessonNote::create([
            'user_id' => $user->id,
            'course_id' => $lesson->course_id,
            'lesson_id' => $lessonId,
            'content' => $data['content'],
            'note_time_second' => $data['note_time_second'] ?? 0,
        ]);
    }

    public function updateLessonNote(int $noteId, array $data, User $user): \App\Models\LessonNote
    {
        $note = \App\Models\LessonNote::where('id', $noteId)
            ->where('user_id', $user->id)
            ->first();

        if (!$note) {
            throw new BusinessException('Không tìm thấy ghi chú.', 404);
        }

        $note->update([
            'content' => $data['content'] ?? $note->content,
            'note_time_second' => isset($data['note_time_second']) ? $data['note_time_second'] : $note->note_time_second,
        ]);

        return $note;
    }

    public function deleteLessonNote(int $noteId, User $user): bool
    {
        $note = \App\Models\LessonNote::where('id', $noteId)
            ->where('user_id', $user->id)
            ->first();

        if (!$note) {
            throw new BusinessException('Không tìm thấy ghi chú.', 404);
        }

        return (bool) $note->delete();
    }

    public function getLearningStreak(?User $user = null): array
    {
        $today = now()->format('Y-m-d');
        if (! $user) {
            $startOfWeek = now()->startOfWeek(\Carbon\Carbon::MONDAY);
            $weekDays = [];
            $dayLabels = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
            for ($i = 0; $i < 7; $i++) {
                $dStr = (clone $startOfWeek)->addDays($i)->format('Y-m-d');
                $weekDays[] = [
                    'day' => $dayLabels[$i],
                    'date' => $dStr,
                    'active' => false,
                    'isToday' => ($dStr === $today),
                    'is_today' => ($dStr === $today),
                ];
            }
            return [
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_active_days' => 0,
                'is_maintaining' => false,
                'status_label' => 'Chưa bắt đầu',
                'completed_days_in_week' => 0,
                'total_days_in_week' => 7,
                'week_days' => $weekDays,
                'encouragement' => [
                    'days_needed' => 7,
                    'next_milestone' => 7,
                    'badge_name' => 'Chiến binh Chăm chỉ',
                    'message' => 'Hãy bắt đầu bài học đầu tiên hôm nay để thiết lập chuỗi học tập!',
                ],
            ];
        }

        $loginDates = [$today];

        if (!empty($user->last_login_at)) {
            $loginDates[] = \Carbon\Carbon::parse($user->last_login_at)->format('Y-m-d');
        }

        try {
            if (empty($user->last_login_at) || \Carbon\Carbon::parse($user->last_login_at)->format('Y-m-d') !== $today) {
                \DB::table('users')->where('id', $user->id)->update(['last_login_at' => now()]);
            }
        } catch (\Throwable $e) {}

        $progressDates = [];
        try {
            if (\Schema::hasTable('lesson_progress')) {
                $progressDates = \DB::table('lesson_progress')
                    ->where('user_id', $user->id)
                    ->selectRaw('DISTINCT DATE(updated_at) as d')
                    ->pluck('d')
                    ->filter()
                    ->toArray();
            }
        } catch (\Throwable $e) {}

        $videoDates = [];
        try {
            if (\Schema::hasTable('video_progress')) {
                $videoDates = \DB::table('video_progress')
                    ->where('user_id', $user->id)
                    ->selectRaw('DISTINCT DATE(updated_at) as d')
                    ->pluck('d')
                    ->filter()
                    ->toArray();
            }
        } catch (\Throwable $e) {}

        $enrollmentDates = [];
        try {
            if (\Schema::hasTable('enrollments')) {
                $enrollmentDates = \DB::table('enrollments')
                    ->where('user_id', $user->id)
                    ->selectRaw('DISTINCT DATE(created_at) as d')
                    ->pluck('d')
                    ->filter()
                    ->toArray();
            }
        } catch (\Throwable $e) {}

        $loginSessionDates = [];
        try {
            if (\Schema::hasTable('auth_sessions')) {
                $loginSessionDates = \DB::table('auth_sessions')
                    ->where('user_id', $user->id)
                    ->selectRaw('DISTINCT DATE(created_at) as d')
                    ->pluck('d')
                    ->filter()
                    ->toArray();
            }
        } catch (\Throwable $e) {}

        $allDatesSet = array_unique(array_filter(array_merge($loginSessionDates, $progressDates, $videoDates, $enrollmentDates, $loginDates)));
        rsort($allDatesSet);
        $yesterday = now()->subDay()->format('Y-m-d');

        $currentStreak = 0;
        $checkDate = now();
        $isTodayActive = in_array($today, $allDatesSet, true);
        $isYesterdayActive = in_array($yesterday, $allDatesSet, true);

        $isMaintaining = $isTodayActive || $isYesterdayActive;

        if (!$isTodayActive && $isYesterdayActive) {
            $checkDate = now()->subDay();
        }

        if ($isMaintaining) {
            while (true) {
                $dStr = $checkDate->format('Y-m-d');
                if (in_array($dStr, $allDatesSet, true)) {
                    $currentStreak++;
                    $checkDate->subDay();
                } else {
                    break;
                }
            }
        }

        if ($currentStreak === 0 && $isMaintaining) {
            $currentStreak = 1;
        }
        if ($currentStreak === 0 && count($allDatesSet) > 0) {
            $currentStreak = 1;
        }

        $longestStreak = max($currentStreak, count($allDatesSet));
        $totalActiveDays = count($allDatesSet);

        $startOfWeek = now()->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekDays = [];
        $dayLabels = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
        $completedDaysCount = 0;

        for ($i = 0; $i < 7; $i++) {
            $currentDayDate = (clone $startOfWeek)->addDays($i);
            $dStr = $currentDayDate->format('Y-m-d');
            $isActive = in_array($dStr, $allDatesSet, true);
            $isToday = ($dStr === $today);

            if ($isActive) {
                $completedDaysCount++;
            }

            $weekDays[] = [
                'day' => $dayLabels[$i],
                'date' => $dStr,
                'active' => $isActive,
                'isToday' => $isToday,
                'is_today' => $isToday,
            ];
        }

        $daysNeeded = max(0, 7 - $currentStreak);
        $nextMilestone = 7;
        $badgeName = 'Chiến binh Chăm chỉ';

        $message = $daysNeeded > 0
            ? "Học thêm {$daysNeeded} ngày nữa để đạt mốc {$nextMilestone} ngày liên tiếp và mở khóa huy hiệu {$badgeName}!"
            : "Chúc mừng bạn đã hoàn thành xuất sắc chuỗi 7 ngày học liên tiếp trong tuần!";

        return [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'total_active_days' => $totalActiveDays,
            'is_maintaining' => $isMaintaining,
            'status_label' => $isMaintaining ? 'Đang duy trì' : 'Chưa bắt đầu',
            'completed_days_in_week' => $completedDaysCount,
            'total_days_in_week' => 7,
            'week_days' => $weekDays,
            'encouragement' => [
                'days_needed' => $daysNeeded,
                'next_milestone' => $nextMilestone,
                'badge_name' => $badgeName,
                'message' => $message,
            ],
        ];
    }
}

