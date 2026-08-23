<?php

namespace App\Repositories\Report;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\Order;
use App\Models\CourseReview;
use Illuminate\Support\Facades\DB;

class CourseAnalyticsRepository
{
    public function getCourseForInstructor(int $courseId, int $instructorId): ?Course
    {
        return Course::where('id', $courseId)
            
            ->first();
    }

    public function getEnrollmentMetrics(int $courseId, ?string $fromDate, ?string $toDate): object
    {
        $query = Enrollment::where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed']);

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate . ' 23:59:59');
        }

        $total = $query->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $avgProgress = $query->avg('progress_percent') ?? 0;

        return (object) [
            'total' => $total,
            'completed' => $completed,
            'avg_progress' => round((float) $avgProgress, 2),
        ];
    }

    public function getQuizMetrics(int $courseId, ?string $fromDate, ?string $toDate): object
    {
        $query = QuizAttempt::whereHas('quiz.lesson', function ($q) use ($courseId) {
            $q->where('course_id', $courseId);
        });

        if ($fromDate) {
            $query->where('submitted_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('submitted_at', '<=', $toDate . ' 23:59:59');
        }

        $totalAttempts = $query->count();
        $passedCount = (clone $query)->where('passed', true)->count();

        return (object) [
            'total_attempts' => $totalAttempts,
            'passed_count' => $passedCount,
        ];
    }

    public function getRevenueMetrics(int $courseId, ?string $fromDate, ?string $toDate): object
    {
        // Calculate revenue for this course
        // Using Order items or Revenue table. Assuming Revenue table exists based on ERD scope.
        // The prompt mentions "revenues.instructor_amount" etc.
        $query = DB::table('revenues')
            ->join('orders', 'revenues.order_id', '=', 'orders.id')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.item_type', 'course')
            ->where('order_items.item_id', $courseId)
            ->where('revenues.status', 'completed');

        if ($fromDate) {
            $query->where('revenues.earned_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('revenues.earned_at', '<=', $toDate . ' 23:59:59');
        }

        $instructorAmount = $query->sum('revenues.instructor_amount');

        return (object) [
            'instructor_amount' => (float) $instructorAmount,
        ];
    }

    public function getReviewMetrics(int $courseId, ?string $fromDate, ?string $toDate): object
    {
        $query = CourseReview::where('course_id', $courseId);

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate . ' 23:59:59');
        }

        $total = $query->count();
        $avgRating = $query->avg('rating') ?? 0;

        return (object) [
            'total' => $total,
            'avg_rating' => round((float) $avgRating, 1),
        ];
    }
}
