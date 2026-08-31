<?php

namespace App\Repositories\Report;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class InstructorReportRepository
{
    public function getCompletionRates(array $filters, $user)
    {
        $query = Course::query()
            ->select('courses.id', 'courses.title')
            ->selectRaw('COUNT(enrollments.id) as total_enrollments')
            ->selectRaw('SUM(CASE WHEN enrollments.status = "completed" THEN 1 ELSE 0 END) as completed_enrollments')
            ->selectRaw('COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM lesson_progress WHERE lesson_progress.enrollment_id = enrollments.id) THEN enrollments.id ELSE NULL END) as started_enrollments')
            ->selectRaw('IFNULL(AVG(enrollments.progress_percent), 0) as avg_progress')
            ->leftJoin('enrollments', function ($join) use ($filters) {
                $join->on('courses.id', '=', 'enrollments.course_id');
                if (!empty($filters['date_from'])) {
                    $join->whereDate('enrollments.enrolled_at', '>=', $filters['date_from']);
                }
                if (!empty($filters['date_to'])) {
                    $join->whereDate('enrollments.enrolled_at', '<=', $filters['date_to']);
                }
                if (!empty($filters['month'])) {
                    $join->whereMonth('enrollments.enrolled_at', $filters['month']);
                }
                if (!empty($filters['year'])) {
                    $join->whereYear('enrollments.enrolled_at', $filters['year']);
                }
            })
            ->groupBy('courses.id', 'courses.title');

        if ($user->role === 'instructor') {
            $query->where('courses.instructor_id', $user->id);
        }

        if (!empty($filters['course_id'])) {
            $query->where('courses.id', $filters['course_id']);
        }

        $perPage = max(1, (int) ($filters['per_page'] ?? 15));

        return $query->paginate($perPage);
    }
}