<?php

namespace App\Repositories\Report;

use Illuminate\Support\Facades\DB;

class InstructorTopCourseRepository
{
    public function getTopCourses(int $instructorId, array $filters): array
    {
        $limit = min(max((int) ($filters['limit'] ?? 10), 1), 20);

        $query = DB::table('courses')
            ->leftJoin('enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'courses.id')
                    ->whereIn('enrollments.status', ['active', 'completed']);
            })
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->selectRaw('
                courses.id as course_id,
                courses.title,
                courses.status,
                COUNT(enrollments.id) as enrollment_count,
                COUNT(DISTINCT enrollments.user_id) as unique_learner_count
            ')
            ->groupBy('courses.id', 'courses.title', 'courses.status')
            ->orderByDesc('enrollment_count')
            ->limit($limit);

        if (!empty($filters['date_from'])) {
            $query->whereDate('enrollments.enrolled_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('enrollments.enrolled_at', '<=', $filters['date_to']);
        }

        return $query->get()->map(fn ($row) => [
            'course_id' => (int) $row->course_id,
            'title' => $row->title,
            'status' => $row->status,
            'enrollment_count' => (int) $row->enrollment_count,
            'unique_learner_count' => (int) $row->unique_learner_count,
        ])->all();
    }
}