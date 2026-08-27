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
            
            ->whereBetween('enrollments.enrolled_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
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

        $rawMap = $query->get()->keyBy('period');

        $result = [];
        if ($groupBy === 'month') {
            $year = $startDate->year;
            for ($m = 1; $m <= 12; $m++) {
                $periodKey = sprintf('%04d-%02d', $year, $m);
                $row = $rawMap->get($periodKey);
                $result[] = [
                    'period' => $periodKey,
                    'enrollment_count' => (int) ($row ? $row->enrollment_count : 0),
                    'completed_count' => (int) ($row ? $row->completed_count : 0),
                ];
            }
        } else {
            $curr = $startDate->copy()->startOfDay();
            $end = $endDate->copy()->startOfDay();
            while ($curr->lte($end)) {
                $periodKey = $curr->format('Y-m-d');
                $row = $rawMap->get($periodKey);
                $result[] = [
                    'period' => $periodKey,
                    'enrollment_count' => (int) ($row ? $row->enrollment_count : 0),
                    'completed_count' => (int) ($row ? $row->completed_count : 0),
                ];
                $curr->addDay();
            }
        }

        return $result;
    }

    private function resolveDateRange(array &$filters): array
    {
        $period = $filters['preset'] ?? $filters['period'] ?? null;
        if ($period === 'day') {
            $filters['group_by'] = $filters['group_by'] ?? 'day';
            return [now()->startOfDay(), now()->endOfDay()];
        }
        if ($period === 'week') {
            $filters['group_by'] = $filters['group_by'] ?? 'day';
            return [now()->startOfWeek(), now()->endOfWeek()];
        }
        if ($period === 'month') {
            $filters['group_by'] = $filters['group_by'] ?? 'day';
            return [now()->startOfMonth(), now()->endOfMonth()];
        }
        if ($period === 'year') {
            $filters['group_by'] = $filters['group_by'] ?? 'month';
            return [now()->startOfYear(), now()->endOfYear()];
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            return [Carbon::parse($filters['date_from']), Carbon::parse($filters['date_to'])];
        }

        return [now()->startOfMonth(), now()->endOfMonth()];
    }
}