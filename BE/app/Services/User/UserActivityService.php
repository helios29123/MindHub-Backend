<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

final class UserActivityService
{
    /**
     * Record activity when a user completes a lesson.
     */
    public function recordLessonCompletion(User $user, Lesson $lesson): void
    {
        $today = Carbon::now()->toDateString();
        $yesterday = Carbon::now()->subDay()->toDateString();

        // 1. Award +10 XP for completing a lesson
        $user->xp = ($user->xp ?? 0) + 10;

        // 2. Daily Mission check (Goal: 2 lessons completed today)
        if ($user->last_mission_completed_date !== $today) {
            $lessonsCompletedToday = LessonProgress::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->count();

            // Note: Since this is called after updating the DB, the count includes the current lesson.
            if ($lessonsCompletedToday >= 2) {
                $user->xp += 50;
                $user->last_mission_completed_date = $today;
            }
        }

        // 3. Update streak
        if (!$user->last_active_date) {
            $user->streak_count = 1;
        } elseif ($user->last_active_date === $yesterday) {
            $user->streak_count += 1;
        } elseif ($user->last_active_date !== $today) {
            $user->streak_count = 1;
        }
        $user->last_active_date = $today;
        $user->save();
    }

    /**
     * Update/reset streak based on last active date.
     */
    public function updateStreak(User $user): void
    {
        $today = Carbon::now()->toDateString();
        $yesterday = Carbon::now()->subDay()->toDateString();

        if (!$user->last_active_date) {
            $user->streak_count = 0;
            $user->save();
        } elseif ($user->last_active_date !== $today && $user->last_active_date !== $yesterday) {
            $user->streak_count = 0;
            $user->save();
        }
    }

    /**
     * Get activity data for the dashboard.
     */
    public function getActivityDashboardData(User $user): array
    {
        // Update user streak first
        $this->updateStreak($user);

        $now = Carbon::now();
        $daysInMonth = $now->daysInMonth;
        $year = $now->year;
        $monthName = $now->translatedFormat('F'); // translated name (e.g. Tháng 8)
        
        // 0 = Monday, 6 = Sunday (Vite React expects T2 = 0, CN = 6)
        $firstDayOfMonth = Carbon::now()->startOfMonth();
        $firstDayOfWeek = ($firstDayOfMonth->dayOfWeek + 6) % 7; 

        // Fetch all completed lessons for this user to map to current month
        $completions = DB::table('lesson_progress as lp')
            ->join('lessons as l', 'lp.lesson_id', '=', 'l.id')
            ->select(
                DB::raw('DATE(lp.completed_at) as completed_date'),
                DB::raw('SUM(CASE WHEN l.lesson_type = "video" THEN COALESCE(l.video_duration_seconds, 600) ELSE 300 END) as duration'),
                DB::raw('COUNT(*) as count')
            )
            ->where('lp.user_id', $user->id)
            ->where('lp.status', 'completed')
            ->groupBy('completed_date')
            ->get();

        $activities = [];
        $currentYearMonth = $now->format('Y-m-');

        foreach ($completions as $c) {
            $day = date('d', strtotime($c->completed_date));
            $mappedDate = $currentYearMonth . $day;
            $activities[$mappedDate] = [
                'date' => $mappedDate,
                'duration_seconds' => (int) $c->duration,
                'lessons_count' => (int) $c->count,
            ];
        }

        // Daily mission status
        $today = Carbon::now()->toDateString();
        $lessonsCompletedToday = LessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->count();

        $isMissionCompleted = $user->last_mission_completed_date === $today || $lessonsCompletedToday >= 2;

        // User stats
        $completedCoursesCount = DB::table('enrollments')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $activeCoursesCount = DB::table('enrollments')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $totalDurationSeconds = DB::table('lesson_progress')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('learning_duration_seconds');
        $learningHours = round($totalDurationSeconds / 3600, 1);

        $certificatesCount = DB::table('certificates')
            ->where('user_id', $user->id)
            ->count();

        // Leaderboard (top 5 users by XP)
        $topUsers = User::where('role', 'learner')
            ->orderByDesc('xp')
            ->take(5)
            ->get();

        $leaderboard = [];
        $rank = 1;
        $userRank = null;
        $userInTop = false;

        foreach ($topUsers as $topUser) {
            $isMe = $topUser->id === $user->id;
            if ($isMe) {
                $userRank = $rank;
                $userInTop = true;
            }
            $leaderboard[] = [
                'id' => $topUser->id,
                'name' => $topUser->full_name,
                'xp' => (int) $topUser->xp,
                'avatar' => $topUser->avatar_url,
                'rank' => $rank,
                'isMe' => $isMe,
            ];
            $rank++;
        }

        // If user is not in top 5, calculate their rank
        if (!$userInTop && $user->role === 'learner') {
            $userRank = User::where('role', 'learner')
                ->where('xp', '>', $user->xp)
                ->count() + 1;

            $leaderboard[] = [
                'id' => $user->id,
                'name' => $user->full_name,
                'xp' => (int) $user->xp,
                'avatar' => $user->avatar_url,
                'rank' => $userRank,
                'isMe' => true,
            ];
        }

        return [
            'month' => $monthName,
            'month_number' => $now->month,
            'year' => $year,
            'days_in_month' => $daysInMonth,
            'first_day_of_week' => $firstDayOfWeek,
            'activities' => (object) $activities,
            'daily_mission' => [
                'description' => 'Hoàn thành 2 bài học',
                'target_count' => 2,
                'current_count' => min($lessonsCompletedToday, 2),
                'xp_reward' => 50,
                'completed' => $isMissionCompleted,
            ],
            'stats' => [
                'total_xp' => (int) ($user->xp ?? 0),
                'streak_count' => (int) ($user->streak_count ?? 0),
                'completed_courses_count' => $completedCoursesCount,
                'active_courses_count' => $activeCoursesCount,
                'learning_hours' => $learningHours,
                'certificates_count' => $certificatesCount,
            ],
            'leaderboard' => $leaderboard,
        ];
    }
}
