<?php

namespace App\Repositories\Report;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InstructorDashboardRepository
{
    public function getDashboard(int $instructorId, array $filters): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($filters);

        $viewsData = $this->viewsSummary($instructorId, $startDate, $endDate);

        return [
            'course_summary' => $this->courseSummary($instructorId),
            'enrollment_summary' => $this->enrollmentSummary($instructorId, $startDate, $endDate),
            'revenue_summary' => $this->revenueSummary($instructorId, $startDate, $endDate),
            'withdraw_summary' => $this->withdrawSummary($instructorId),
            'interaction_summary' => [
                'views' => $viewsData['views'],
                'views_previous_period' => $viewsData['views_previous_period'],
                'views_change_percentage' => $viewsData['views_change_percentage'],
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

        $published = (int) ($rows['published'] ?? 0) + (int) ($rows['approved'] ?? 0) + (int) ($rows['active'] ?? 0);
        $draft = (int) ($rows['draft'] ?? 0);
        $pendingReview = (int) ($rows['pending_review'] ?? 0) + (int) ($rows['pending'] ?? 0) + (int) ($rows['submitted'] ?? 0);
        $rejected = (int) ($rows['rejected'] ?? 0);
        $approved = (int) ($rows['approved'] ?? 0);
        $hidden = (int) ($rows['hidden'] ?? 0) + (int) ($rows['inactive'] ?? 0);

        return [
            'total' => array_sum(array_map('intval', $rows)),
            'published' => $published,
            'draft' => $draft,
            'pending_review' => $pendingReview,
            'pending' => $pendingReview,
            'rejected' => $rejected,
            'approved' => $approved,
            'hidden' => $hidden,
        ];
    }

    private function enrollmentSummary(int $instructorId, Carbon $startDate, Carbon $endDate): array
    {
        $query = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->whereIn('enrollments.status', ['active', 'completed']);

        $totalEnrollments = (int) (clone $query)->count();
        $uniqueLearners = (int) (clone $query)->distinct('enrollments.user_id')->count('enrollments.user_id');
        $activeEnrollments = (int) (clone $query)->where('enrollments.status', 'active')->count();
        $completedEnrollments = (int) (clone $query)->where('enrollments.status', 'completed')->count();

        $newThisMonth = (int) (clone $query)->whereBetween('enrollments.enrolled_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])->count();

        $diffDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffDays);
        $prevEndDate = $startDate->copy()->subDay();

        $previousPeriodEnrollments = (int) (clone $query)->whereBetween('enrollments.enrolled_at', [$prevStartDate->startOfDay(), $prevEndDate->endOfDay()])->count();

        return [
            'total_enrollments' => $totalEnrollments,
            'unique_learners' => $uniqueLearners,
            'total_students' => $uniqueLearners,
            'active_enrollments' => $activeEnrollments,
            'completed_enrollments' => $completedEnrollments,
            'new_this_month' => $newThisMonth,
            'previous_period_enrollments' => $previousPeriodEnrollments,
            'change_percentage' => $this->calculateChangePercentage($newThisMonth, $previousPeriodEnrollments),
        ];
    }

    private function revenueSummary(int $instructorId, Carbon $startDate, Carbon $endDate): array
    {
        $row = DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->whereBetween('earned_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->whereIn('status', ['available', 'withdrawn'])
            ->selectRaw('
                COALESCE(SUM(gross_amount), 0) as gross_amount_this_month,
                COALESCE(SUM(instructor_amount), 0) as instructor_amount_this_month,
                COALESCE(SUM(platform_fee_amount), 0) as platform_fee_this_month
            ')
            ->first();

        $diffDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffDays);
        $prevEndDate = $startDate->copy()->subDay();

        $prevRow = DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->whereBetween('earned_at', [$prevStartDate->startOfDay(), $prevEndDate->endOfDay()])
            ->whereIn('status', ['available', 'withdrawn'])
            ->selectRaw('COALESCE(SUM(instructor_amount), 0) as previous_period_instructor_amount')
            ->first();

        $totalRow = DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->whereIn('status', ['available', 'withdrawn'])
            ->selectRaw('COALESCE(SUM(instructor_amount), 0) as total_instructor_amount')
            ->first();

        $currentAmount = (float) ($row->instructor_amount_this_month ?? 0);
        $previousAmount = (float) ($prevRow->previous_period_instructor_amount ?? 0);

        return [
            'gross_amount_this_month' => $this->money($row->gross_amount_this_month ?? 0),
            'instructor_amount_this_month' => $this->money($currentAmount),
            'total_instructor_amount' => $this->money($totalRow->total_instructor_amount ?? 0),
            'platform_fee_this_month' => $this->money($row->platform_fee_this_month ?? 0),
            'previous_period_instructor_amount' => $this->money($previousAmount),
            'change_percentage' => $this->calculateChangePercentage($currentAmount, $previousAmount),
        ];
    }

    private function calculateChangePercentage(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
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

    private function viewsSummary(int $instructorId, Carbon $startDate, Carbon $endDate): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('course_views')) {
            return [
                'views' => 0,
                'views_previous_period' => 0,
                'views_change_percentage' => 0,
            ];
        }

        $query = DB::table('course_views')
            ->join('courses', 'courses.id', '=', 'course_views.course_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at');

        $viewsThisPeriod = (int) (clone $query)
            ->whereBetween('course_views.viewed_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->count();

        $diffDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffDays);
        $prevEndDate = $startDate->copy()->subDay();

        $previousPeriodViews = (int) (clone $query)
            ->whereBetween('course_views.viewed_at', [$prevStartDate->startOfDay(), $prevEndDate->endOfDay()])
            ->count();

        return [
            'views' => $viewsThisPeriod,
            'views_previous_period' => $previousPeriodViews,
            'views_change_percentage' => $this->calculateChangePercentage($viewsThisPeriod, $previousPeriodViews),
        ];
    }

    private function countUnansweredQuestions(int $instructorId): int
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('comments')) {
            return 0;
        }

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