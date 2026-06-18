<?php

namespace App\Services\Report;

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
            throw new \App\Exceptions\BusinessException('Không tìm thấy khóa học.', 404);
        }

        if ((int) $course->instructor_id !== $instructorId) {
            throw new \App\Exceptions\BusinessException('Bạn không có quyền xem báo cáo khóa học này.', 403);
        }

        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $enrollmentMetrics = $this->repository->getEnrollmentMetrics($courseId, $fromDate, $toDate);
        $quizMetrics = $this->repository->getQuizMetrics($courseId, $fromDate, $toDate);
        
        // Wrap revenue in try catch just in case tables differ
        $revenueMetrics = null;
        try {
            $revenueMetrics = $this->repository->getRevenueMetrics($courseId, $fromDate, $toDate);
        } catch (\Exception $e) {
            $revenueMetrics = (object) ['instructor_amount' => 0];
        }

        $reviewMetrics = $this->repository->getReviewMetrics($courseId, $fromDate, $toDate);

        $completionRate = $enrollmentMetrics->total > 0 
            ? round(($enrollmentMetrics->completed / $enrollmentMetrics->total) * 100, 2) 
            : 0;

        $quizPassRate = $quizMetrics->total_attempts > 0 
            ? round(($quizMetrics->passed_count / $quizMetrics->total_attempts) * 100, 2) 
            : 0;

        return [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'status' => $course->status,
            ],
            'learning' => [
                'enrollment_count' => $enrollmentMetrics->total,
                'completed_enrollment_count' => $enrollmentMetrics->completed,
                'completion_rate' => $completionRate,
                'average_progress' => $enrollmentMetrics->avg_progress,
            ],
            'quiz' => [
                'quiz_attempt_count' => $quizMetrics->total_attempts,
                'quiz_pass_count' => $quizMetrics->passed_count,
                'quiz_pass_rate' => $quizPassRate,
            ],
            'revenue' => [
                'instructor_amount' => $revenueMetrics->instructor_amount,
            ],
            'review' => [
                'average_rating' => $reviewMetrics->avg_rating,
                'review_count' => $reviewMetrics->total,
            ],
        ];
    }
}
