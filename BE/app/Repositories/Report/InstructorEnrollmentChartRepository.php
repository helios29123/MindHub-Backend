<?php

namespace App\Repositories\Report;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InstructorEnrollmentChartRepository
{
    public function getChart(int $instructorId, array $filters): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($filters);
        $groupBy = $filters['group_by'] ?? 'day';
        $format = $groupBy === 'month' ? '%Y-%m' : '%Y-%m-%d';

        $query = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->whereBetween('enrollments.enrolled_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->selectRaw("
                DATE_FORMAT(enrollments.enrolled_at, '$format') as period,
                COUNT(enrollments.id) as enrollment_count,
                SUM(CASE WHEN enrollments.status = 'completed' THEN 1 ELSE 0 END) as completed_count
            ")
            ->groupBy('period')
            ->orderBy('period');

        if (!empty($filters['course_id'])) {
            $query->where('enrollments.course_id', (int) $filters['course_id']);
        }

        return $query->get()->map(fn ($row) => [
            'period' => $row->period,
            'enrollment_count' => (int) $row->enrollment_count,
            'completed_count' => (int) $row->completed_count,
        ])->all();
    }

    private function resolveDateRange(array $filters): array
    {
        return [
            !empty($filters['date_from']) ? Carbon::parse($filters['date_from']) : now()->subDays(29),
            !empty($filters['date_to']) ? Carbon::parse($filters['date_to']) : now(),
        ];
    }
}