<?php

namespace App\Services\Report;

use App\Exceptions\BusinessException;
use App\Repositories\Report\CourseAnalyticsRepository;

class CourseAnalyticsService
{
    public function __construct(
        private readonly CourseAnalyticsRepository $repository
    ) {
    }

    public function getCourseAnalytics(int $instructorId, int $courseId, array $filters): array
    {
        $course = $this->repository->getCourseForInstructor($courseId, $instructorId);

        if (!$course) {
            throw new BusinessException('Không tìm thấy khóa học hoặc bạn không có quyền truy cập.', 404);
        }

        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $enrollmentMetrics = $this->repository->getEnrollmentMetrics($courseId, $fromDate, $toDate);
        $revenueMetrics = $this->repository->getRevenueMetrics($courseId, $fromDate, $toDate);
        $reviewMetrics = $this->repository->getReviewMetrics($courseId, $fromDate, $toDate);

        // Completion Rate formula: completed / started learning * 100
        $startedCount = $enrollmentMetrics->started ?? 0;
        $completionRate = $startedCount > 0
            ? round(($enrollmentMetrics->completed / $startedCount) * 100, 2)
            : 0.0;

        return [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'status' => $course->status,
            ],
            'learning' => [
                'enrollment_count' => (int) $enrollmentMetrics->total,
                'completed_enrollment_count' => (int) $enrollmentMetrics->completed,
                'completion_rate' => (float) $completionRate,
                'average_progress' => (float) $enrollmentMetrics->avg_progress,
            ],
            'revenue' => [
                'instructor_amount' => (float) $revenueMetrics->instructor_amount,
            ],
            'review' => [
                'average_rating' => (float) $reviewMetrics->avg_rating,
                'review_count' => (int) $reviewMetrics->total,
            ],
        ];
    }
}
