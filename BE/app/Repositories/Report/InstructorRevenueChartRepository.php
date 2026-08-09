<?php

namespace App\Repositories\Report;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InstructorRevenueChartRepository
{
    public function getChart(int $instructorId, array $filters): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($filters);
        $groupBy = $filters['group_by'] ?? 'day';
        $format = $groupBy === 'month' ? '%Y-%m' : '%Y-%m-%d';

        $query = DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->whereBetween('earned_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->whereIn('status', ['pending', 'available', 'scheduled', 'included_in_payout', 'paid', 'withdrawn'])
            ->selectRaw("
                DATE_FORMAT(earned_at, '$format') as period,
                COALESCE(SUM(gross_amount), 0) as gross_amount,
                COALESCE(SUM(instructor_amount), 0) as instructor_amount,
                COALESCE(SUM(platform_fee_amount), 0) as platform_fee_amount
            ")
            ->groupBy('period')
            ->orderBy('period');

        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
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
                    'gross_amount' => $this->money($row ? $row->gross_amount : 0),
                    'instructor_amount' => $this->money($row ? $row->instructor_amount : 0),
                    'platform_fee_amount' => $this->money($row ? $row->platform_fee_amount : 0),
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
                    'gross_amount' => $this->money($row ? $row->gross_amount : 0),
                    'instructor_amount' => $this->money($row ? $row->instructor_amount : 0),
                    'platform_fee_amount' => $this->money($row ? $row->platform_fee_amount : 0),
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

    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}