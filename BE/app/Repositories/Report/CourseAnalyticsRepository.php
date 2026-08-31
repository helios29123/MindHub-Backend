<?php

namespace App\Repositories\Report;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class CourseAnalyticsRepository
{
    public function getCourseForInstructor(int $courseId, int $instructorId): ?Course
    {
        return Course::where('id', $courseId)
            ->where('instructor_id', $instructorId)
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

        $total = (clone $query)->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $started = (clone $query)->whereExists(function ($sq) {
            $sq->select(DB::raw(1))
                ->from('lesson_progress')
                ->whereColumn('lesson_progress.enrollment_id', 'enrollments.id');
        })->count();
        $avgProgress = (clone $query)->avg('progress_percent') ?? 0;

        return (object) [
            'total' => $total,
            'completed' => $completed,
            'started' => $started,
            'avg_progress' => round((float) $avgProgress, 2),
        ];
    }

    public function getRevenueMetrics(int $courseId, ?string $fromDate, ?string $toDate): object
    {
        $query = DB::table('revenues')
            ->where('course_id', $courseId);

        if ($fromDate) {
            $query->where('earned_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('earned_at', '<=', $toDate . ' 23:59:59');
        }

        $instructorAmount = $query->sum('instructor_amount') ?? 0;

        return (object) [
            'instructor_amount' => (float) $instructorAmount,
        ];
    }

    public function getReviewMetrics(int $courseId, ?string $fromDate, ?string $toDate): object
    {
        $query = DB::table('course_reviews')
            ->join('orders', 'course_reviews.order_id', '=', 'orders.id')
            ->where('orders.course_id', $courseId);

        if ($fromDate) {
            $query->where('course_reviews.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('course_reviews.created_at', '<=', $toDate . ' 23:59:59');
        }

        $total = (clone $query)->count();
        $avgRating = (clone $query)->avg('course_reviews.rating') ?? 0;

        return (object) [
            'total' => $total,
            'avg_rating' => round((float) $avgRating, 1),
        ];
    }
}
