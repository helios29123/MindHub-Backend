<?php

namespace App\Repositories\Report;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InstructorDashboardRepository
{
    public function getDashboard(int $instructorId, array $filters): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($filters);

        return [
            'course_summary' => $this->courseSummary($instructorId),
            'enrollment_summary' => $this->enrollmentSummary($instructorId),
            'revenue_summary' => $this->revenueSummary($instructorId, $startDate, $endDate),
            'withdraw_summary' => $this->withdrawSummary($instructorId),
            'interaction_summary' => [
                'unanswered_questions' => $this->countUnansweredQuestions($instructorId),
            ],
            'filters' => [
                'date_from' => $startDate->toDateString(),
                'date_to' => $endDate->toDateString(),
            ],
        ];
    }

    private function courseSummary(int $instructorId): array
    {
        $rows = DB::table('courses')
            ->selectRaw('status, COUNT(*) as total')
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'total' => array_sum(array_map('intval', $rows)),
            'published' => (int) ($rows['published'] ?? 0),
            'draft' => (int) ($rows['draft'] ?? 0),
            'pending_review' => (int) ($rows['pending_review'] ?? 0),
            'rejected' => (int) ($rows['rejected'] ?? 0),
            'approved' => (int) ($rows['approved'] ?? 0),
            'hidden' => (int) ($rows['hidden'] ?? 0),
        ];
    }

    private function enrollmentSummary(int $instructorId): array
    {
        $query = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->whereIn('enrollments.status', ['active', 'completed']);

        return [
            'total_enrollments' => (int) (clone $query)->count(),
            'active_enrollments' => (int) (clone $query)->where('enrollments.status', 'active')->count(),
            'completed_enrollments' => (int) (clone $query)->where('enrollments.status', 'completed')->count(),
        ];
    }

    private function revenueSummary(int $instructorId, Carbon $startDate, Carbon $endDate): array
    {
        $row = DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->whereBetween('earned_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereIn('status', ['available', 'withdrawn'])
            ->selectRaw('
                COALESCE(SUM(gross_amount), 0) as gross_amount_this_month,
                COALESCE(SUM(instructor_amount), 0) as instructor_amount_this_month,
                COALESCE(SUM(platform_fee_amount), 0) as platform_fee_this_month
            ')
            ->first();

        return [
            'gross_amount_this_month' => $this->money($row->gross_amount_this_month ?? 0),
            'instructor_amount_this_month' => $this->money($row->instructor_amount_this_month ?? 0),
            'platform_fee_this_month' => $this->money($row->platform_fee_this_month ?? 0),
        ];
    }

    private function withdrawSummary(int $instructorId): array
    {
        $available = (float) DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->where('status', 'available')
            ->sum('instructor_amount');

        $pending = (float) DB::table('withdraw_requests')
            ->where('user_id', $instructorId)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        return [
            'available_revenue' => $this->money($available),
            'pending_withdraw_amount' => $this->money($pending),
            'available_balance' => $this->money(max($available - $pending, 0)),
        ];
    }

    private function countUnansweredQuestions(int $instructorId): int
    {
        return (int) DB::table('comments as q')
            ->join('users as learner', 'learner.id', '=', 'q.user_id')
            ->join('lessons', 'lessons.id', '=', 'q.lesson_id')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->whereNull('q.parent_id')
            ->where('q.status', 'visible')
            ->where('learner.role', 'learner')
            ->whereNotExists(function ($sub) use ($instructorId): void {
                $sub->select(DB::raw(1))
                    ->from('comments as r')
                    ->whereColumn('r.parent_id', 'q.id')
                    ->where('r.user_id', $instructorId)
                    ->where('r.status', 'visible');
            })
            ->count();
    }

    private function resolveDateRange(array $filters): array
    {
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            return [
                Carbon::parse($filters['date_from']),
                Carbon::parse($filters['date_to']),
            ];
        }

        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);

        $date = Carbon::create($year, $month, 1);

        return [
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
        ];
    }

    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}