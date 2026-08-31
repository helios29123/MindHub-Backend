<?php

namespace App\Repositories\Report;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstructorDashboardRepository
{
    public function getDashboard(int $instructorId, array $filters): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($filters);

        $courseSummary = $this->courseSummary($instructorId);
        $enrollmentSummary = $this->enrollmentSummary($instructorId, $startDate, $endDate);
        $revenueSummary = $this->revenueSummary($instructorId, $startDate, $endDate);
        $withdrawSummary = $this->withdrawSummary($instructorId);
        $viewsSummary = $this->viewsSummary($instructorId, $startDate, $endDate);

        $unansweredQuestions = 0;
        if (Schema::hasTable('comments')) {
            $unansweredQuestions = (int) DB::table('comments')
                ->join('lessons', 'lessons.id', '=', 'comments.lesson_id')
                ->join('courses', 'courses.id', '=', 'lessons.course_id')
                ->where('courses.instructor_id', $instructorId)
                ->whereNull('comments.parent_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('comments as replies')
                        ->whereColumn('replies.parent_id', 'comments.id');
                })
                ->count();
        }

        return [
            'course_summary' => $courseSummary,
            'enrollment_summary' => $enrollmentSummary,
            'revenue_summary' => $revenueSummary,
            'withdraw_summary' => $withdrawSummary,
            'interaction_summary' => [
                'views' => $viewsSummary['views'],
                'views_previous_period' => $viewsSummary['views_previous_period'],
                'views_change_percentage' => $viewsSummary['views_change_percentage'],
                'unanswered_questions' => $unansweredQuestions,
            ],
        ];
    }

    private function courseSummary(int $instructorId): array
    {
        $courses = DB::table('courses')
            ->where('instructor_id', $instructorId)
            ->select('status')
            ->get();

        $total = $courses->count();
        $published = $courses->where('status', 'published')->count();
        $draft = $courses->where('status', 'draft')->count();
        $pending = $courses->whereIn('status', ['pending', 'pending_review'])->count();
        $rejected = $courses->where('status', 'rejected')->count();
        $approved = $courses->where('status', 'approved')->count();
        $hidden = $courses->whereIn('status', ['hidden', 'inactive'])->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'pending_review' => $pending,
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
            ->whereIn('enrollments.status', ['active', 'completed']);

        $totalEnrollments = (clone $query)->count();
        $uniqueLearners = (clone $query)->distinct('enrollments.user_id')->count('enrollments.user_id');
        $activeEnrollments = (clone $query)->where('enrollments.status', 'active')->count();
        $completedEnrollments = (clone $query)->where('enrollments.status', 'completed')->count();

        $newThisMonth = (clone $query)
            ->whereBetween('enrollments.enrolled_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->count();

        $diffDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffDays);
        $prevEndDate = $startDate->copy()->subDay();

        $previousPeriod = (clone $query)
            ->whereBetween('enrollments.enrolled_at', [$prevStartDate->startOfDay(), $prevEndDate->endOfDay()])
            ->count();

        $newThisYear = (clone $query)
            ->whereBetween('enrollments.enrolled_at', [now()->startOfYear(), now()->endOfYear()])
            ->count();

        return [
            'total_enrollments' => $totalEnrollments,
            'unique_learners' => $uniqueLearners,
            'total_students' => $uniqueLearners,
            'active_enrollments' => $activeEnrollments,
            'completed_enrollments' => $completedEnrollments,
            'new_this_month' => $newThisMonth,
            'new_this_year' => $newThisYear,
            'previous_period_enrollments' => $previousPeriod,
            'change_percentage' => $this->calculateChangePercentage((float) $newThisMonth, (float) $previousPeriod),
        ];
    }

    private function revenueSummary(int $instructorId, Carbon $startDate, Carbon $endDate): array
    {
        $row = DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->whereBetween('earned_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
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
            ->selectRaw('COALESCE(SUM(instructor_amount), 0) as previous_period_instructor_amount')
            ->first();

        $totalRow = DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->selectRaw('COALESCE(SUM(instructor_amount), 0) as total_instructor_amount')
            ->first();

        $yearRow = DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->whereBetween('earned_at', [now()->startOfYear(), now()->endOfYear()])
            ->selectRaw('COALESCE(SUM(instructor_amount), 0) as instructor_amount_this_year')
            ->first();

        $curr = (float) ($row->instructor_amount_this_month ?? 0);
        $prev = (float) ($prevRow->previous_period_instructor_amount ?? 0);

        return [
            'gross_amount_this_month' => $this->money($row->gross_amount_this_month ?? 0),
            'instructor_amount_this_month' => $this->money($row->instructor_amount_this_month ?? 0),
            'instructor_amount_this_year' => $this->money($yearRow->instructor_amount_this_year ?? 0),
            'total_instructor_amount' => $this->money($totalRow->total_instructor_amount ?? 0),
            'platform_fee_this_month' => $this->money($row->platform_fee_this_month ?? 0),
            'previous_period_instructor_amount' => $this->money($prev),
            'change_percentage' => $this->calculateChangePercentage($curr, $prev),
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
            ->sum('instructor_amount');

        $pending = (float) DB::table('withdraw_requests')
            ->where('user_id', $instructorId)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        $paidWithdrawn = (float) DB::table('withdraw_requests')
            ->where('user_id', $instructorId)
            ->where('status', 'paid')
            ->sum('amount');

        $availableBalance = max(0, $available - $paidWithdrawn - $pending);

        return [
            'available_revenue' => $this->money($available),
            'pending_withdraw_amount' => $this->money($pending),
            'available_balance' => $this->money($availableBalance),
        ];
    }

    private function viewsSummary(int $instructorId, Carbon $startDate, Carbon $endDate): array
    {
        if (!Schema::hasTable('course_views')) {
            return [
                'views' => 0,
                'views_previous_period' => 0,
                'views_change_percentage' => 0,
            ];
        }

        $query = DB::table('course_views')
            ->join('courses', 'courses.id', '=', 'course_views.course_id')
            ->where('courses.instructor_id', $instructorId);

        $viewsThisPeriod = (int) (clone $query)
            ->whereBetween('course_views.created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->count();

        $diffDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffDays);
        $prevEndDate = $startDate->copy()->subDay();

        $viewsPreviousPeriod = (int) (clone $query)
            ->whereBetween('course_views.created_at', [$prevStartDate->startOfDay(), $prevEndDate->endOfDay()])
            ->count();

        return [
            'views' => $viewsThisPeriod,
            'views_previous_period' => $viewsPreviousPeriod,
            'views_change_percentage' => $this->calculateChangePercentage((float) $viewsThisPeriod, (float) $viewsPreviousPeriod),
        ];
    }

    private function resolveDateRange(array $filters): array
    {
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            return [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay(),
            ];
        }

        $period = $filters['period'] ?? 'this_month';

        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'last_year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function money(float|string|null $val): string
    {
        return number_format((float) ($val ?? 0), 2, '.', '');
    }
}