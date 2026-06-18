<?php

namespace App\Services\Learning;

use App\Repositories\Learning\LearningDashboardRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LearningDashboardService
{
    public function __construct(
        private readonly LearningDashboardRepository $learningDashboardRepository
    ) {
    }

    public function getDashboard(int $userId, array $filters = []): array
    {
        $limitRecent = (int) ($filters['limit_recent'] ?? 5);
        $limitRecent = max(1, min(20, $limitRecent));

        $includeAlerts = (bool) ($filters['include_alerts'] ?? false);

        $enrollments = $this->learningDashboardRepository->getUserEnrollments($userId);
        $recentLessonProgress = $this->learningDashboardRepository->getRecentLessonProgress($userId, $limitRecent);
        $recentFallback = $recentLessonProgress->isEmpty()
            ? $this->learningDashboardRepository->getRecentEnrollmentFallback($userId, $limitRecent)
            : collect();

        $totalCourses = $enrollments->count();

        $completedCourses = $enrollments
            ->filter(fn ($enrollment): bool => $this->isEnrollmentCompleted($enrollment))
            ->count();

        $inProgressCourses = $enrollments
            ->filter(fn ($enrollment): bool => ! $this->isEnrollmentCompleted($enrollment))
            ->count();

        $averageProgress = $totalCourses > 0
            ? round((float) $enrollments->avg(fn ($enrollment): float => (float) $enrollment->progress_percent), 2)
            : 0.0;

        $recentLearning = $this->buildRecentLearning($recentLessonProgress, $recentFallback);

        $lastAccessedAt = $this->resolveLastAccessedAt($enrollments, $recentLearning);

        $dashboard = [
            'total_courses' => $totalCourses,
            'completed_courses' => $completedCourses,
            'in_progress_courses' => $inProgressCourses,
            'average_progress_percent' => $averageProgress,
            'last_accessed_at' => $lastAccessedAt,
            'recent_learning' => $recentLearning,
            'alerts' => [],
        ];

        if ($includeAlerts) {
            $dashboard['alerts'] = $this->buildAlerts($dashboard);
        }

        return $dashboard;
    }

    private function isEnrollmentCompleted(object $enrollment): bool
    {
        return $enrollment->enrollment_status === 'completed'
            || (float) $enrollment->progress_percent >= 100
            || $enrollment->completed_at !== null;
    }

    private function buildRecentLearning(Collection $recentLessonProgress, Collection $recentFallback): array
    {
        if ($recentLessonProgress->isNotEmpty()) {
            return $recentLessonProgress
                ->map(function ($item): array {
                    return [
                        'source' => 'lesson_progress',
                        'enrollment_id' => (int) $item->enrollment_id,
                        'course_id' => (int) $item->course_id,
                        'course_title' => $item->course_title,
                        'course_thumbnail_url' => $item->course_thumbnail_url,
                        'progress_percent' => (float) $item->progress_percent,
                        'lesson_id' => (int) $item->lesson_id,
                        'lesson_title' => $item->lesson_title,
                        'lesson_type' => $item->lesson_type,
                        'lesson_status' => $item->lesson_status,
                        'video_current_second' => $item->video_current_second !== null
                            ? (int) $item->video_current_second
                            : null,
                        'video_duration_seconds' => $item->video_duration_seconds !== null
                            ? (int) $item->video_duration_seconds
                            : null,
                        'learning_duration_seconds' => $item->learning_duration_seconds !== null
                            ? (int) $item->learning_duration_seconds
                            : 0,
                        'last_accessed_at' => $item->accessed_at,
                    ];
                })
                ->values()
                ->all();
        }

        return $recentFallback
            ->map(function ($item): array {
                return [
                    'source' => 'enrollment',
                    'enrollment_id' => (int) $item->enrollment_id,
                    'course_id' => (int) $item->course_id,
                    'course_title' => $item->course_title,
                    'course_thumbnail_url' => $item->course_thumbnail_url,
                    'progress_percent' => (float) $item->progress_percent,
                    'lesson_id' => null,
                    'lesson_title' => null,
                    'lesson_type' => null,
                    'lesson_status' => null,
                    'video_current_second' => null,
                    'video_duration_seconds' => null,
                    'learning_duration_seconds' => 0,
                    'last_accessed_at' => $item->accessed_at,
                ];
            })
            ->values()
            ->all();
    }

    private function resolveLastAccessedAt(Collection $enrollments, array $recentLearning): ?string
    {
        $dates = collect();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->last_accessed_at !== null) {
                $dates->push((string) $enrollment->last_accessed_at);
            }
        }

        foreach ($recentLearning as $item) {
            if (! empty($item['last_accessed_at'])) {
                $dates->push((string) $item['last_accessed_at']);
            }
        }

        return $dates
            ->filter()
            ->sortDesc()
            ->first();
    }

    private function buildAlerts(array $dashboard): array
    {
        $alerts = [];

        if ((int) $dashboard['total_courses'] === 0) {
            return [
                [
                    'type' => 'empty_learning',
                    'level' => 'info',
                    'message' => 'Bạn chưa có dữ liệu học tập.',
                ],
            ];
        }

        if ((int) $dashboard['in_progress_courses'] > 0 && empty($dashboard['last_accessed_at'])) {
            $alerts[] = [
                'type' => 'no_recent_activity',
                'level' => 'warning',
                'message' => 'Bạn có khóa đang học nhưng chưa có hoạt động gần đây.',
            ];
        }

        if (! empty($dashboard['last_accessed_at'])) {
            $lastAccessedAt = Carbon::parse($dashboard['last_accessed_at']);

            if ($lastAccessedAt->lessThan(now()->subDays(7)) && (int) $dashboard['in_progress_courses'] > 0) {
                $alerts[] = [
                    'type' => 'inactive_7_days',
                    'level' => 'warning',
                    'message' => 'Bạn đã lâu chưa quay lại học. Hãy tiếp tục khóa học còn dang dở.',
                ];
            }
        }

        if ((float) $dashboard['average_progress_percent'] > 0 && (float) $dashboard['average_progress_percent'] < 30) {
            $alerts[] = [
                'type' => 'low_average_progress',
                'level' => 'info',
                'message' => 'Tiến độ học trung bình còn thấp. Hãy ưu tiên hoàn thành các bài học gần nhất.',
            ];
        }

        return $alerts;
    }
}