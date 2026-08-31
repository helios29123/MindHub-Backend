<?php

namespace App\Repositories\Learning;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LearningDashboardRepository
{
    public function getStatistics(int $userId): array
    {
        $activeCourses = DB::table('enrollments')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->count();

        $completedCourses = DB::table('enrollments')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        $dailyActivitySeconds = 0;
        if (Schema::hasTable('learning_daily_activity')) {
            $dailyActivitySeconds = (int) DB::table('learning_daily_activity as lda')
                ->join('enrollments as e', 'e.id', '=', 'lda.enrollment_id')
                ->where('e.user_id', $userId)
                ->sum('lda.video_learning_seconds');
        }

        $lessonProgressSeconds = (int) DB::table('lesson_progress as lp')
            ->join('enrollments as e', 'e.id', '=', 'lp.enrollment_id')
            ->where('e.user_id', $userId)
            ->sum('lp.learning_duration_seconds');

        $totalLearningSeconds = max($dailyActivitySeconds, $lessonProgressSeconds);
        $totalLearningHours = (int) round($totalLearningSeconds / 3600);

        $certificatesCount = 0;
        if (Schema::hasTable('certificates')) {
            $certificatesCount = DB::table('certificates')
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->count();
        }

        return [
            'active_courses' => $activeCourses,
            'completed_courses' => $completedCourses,
            'total_learning_hours' => $totalLearningHours,
            'certificates_count' => $certificatesCount,
        ];
    }

    public function getRecentCourse(int $userId): ?array
    {
        $enrollment = DB::table('enrollments as e')
            ->where('e.user_id', $userId)
            ->whereIn('e.status', ['active', 'completed'])
            ->join('courses as c', 'c.id', '=', 'e.course_id')
            ->leftJoin('course_categories as cc', 'cc.course_id', '=', 'c.id')
            ->leftJoin('categories as cat', 'cat.id', '=', 'cc.category_id')
            ->orderByRaw('COALESCE(e.last_accessed_at, e.created_at) DESC')
            ->select([
                'e.id as enrollment_id',
                'e.course_id',
                'e.progress_percent',
                'c.title',
                'c.thumbnail_url',
                'cat.name as category_name'
            ])
            ->first();

        if (!$enrollment) {
            return null;
        }

        $recentLesson = DB::table('lesson_progress as lp')
            ->join('lessons as l', 'l.id', '=', 'lp.lesson_id')
            ->where('lp.enrollment_id', $enrollment->enrollment_id)
            ->where('l.course_id', $enrollment->course_id)
            ->where('l.status', 'published')
            ->orderByRaw('COALESCE(lp.last_accessed_at, lp.updated_at, lp.created_at) DESC')
            ->select([
                'l.id as lesson_id',
                'l.title',
            ])
            ->first();

        $currentLessonData = null;
        if ($recentLesson) {
            $lessons = DB::table('lessons as l')
                ->leftJoin('course_sections as cs', 'cs.id', '=', 'l.course_section_id')
                ->where('l.course_id', $enrollment->course_id)
                ->where('l.status', 'published')
                ->orderByRaw('COALESCE(cs.sort_order, 0) ASC')
                ->orderBy('l.sort_order', 'ASC')
                ->pluck('l.id')
                ->toArray();

            $index = array_search($recentLesson->lesson_id, $lessons);
            $indexText = $index !== false ? 'Bài ' . ($index + 1) : '';

            $currentLessonData = [
                'lesson_id' => $recentLesson->lesson_id,
                'title' => $recentLesson->title,
                'index_text' => $indexText
            ];
        }

        return [
            'course_id' => (int) $enrollment->course_id,
            'title' => $enrollment->title,
            'thumbnail_url' => $enrollment->thumbnail_url,
            'category_name' => $enrollment->category_name,
            'progress_percent' => (float) $enrollment->progress_percent,
            'current_lesson' => $currentLessonData
        ];
    }

    public function getHeatmap(int $userId, int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $dailyActivityMap = [];
        if (Schema::hasTable('learning_daily_activity')) {
            $dailyRecords = DB::table('learning_daily_activity as lda')
                ->join('enrollments as e', 'e.id', '=', 'lda.enrollment_id')
                ->where('e.user_id', $userId)
                ->whereBetween('lda.activity_date', [$startDate, $endDate])
                ->select(
                    'lda.activity_date as date',
                    DB::raw('SUM(lda.video_learning_seconds) as total_time_seconds')
                )
                ->groupBy('lda.activity_date')
                ->get();

            foreach ($dailyRecords as $dr) {
                $dailyActivityMap[$dr->date] = (int) $dr->total_time_seconds;
            }
        }

        $lpRecords = DB::table('lesson_progress as lp')
            ->join('enrollments as e', 'e.id', '=', 'lp.enrollment_id')
            ->where('e.user_id', $userId)
            ->whereBetween(DB::raw('DATE(COALESCE(lp.last_accessed_at, lp.updated_at))'), [$startDate, $endDate])
            ->select(
                DB::raw('DATE(COALESCE(lp.last_accessed_at, lp.updated_at)) as date'),
                DB::raw('SUM(lp.learning_duration_seconds) as total_time_seconds')
            )
            ->groupBy(DB::raw('DATE(COALESCE(lp.last_accessed_at, lp.updated_at))'))
            ->get();

        foreach ($lpRecords as $lpr) {
            $current = $dailyActivityMap[$lpr->date] ?? 0;
            $dailyActivityMap[$lpr->date] = max($current, (int) $lpr->total_time_seconds);
        }

        $qualifyingStreakDates = [];
        if (Schema::hasTable('lesson_progress') && Schema::hasTable('lessons')) {
            $qualifyingStreakDates = DB::table('lesson_progress as lp')
                ->join('lessons as l', 'l.id', '=', 'lp.lesson_id')
                ->join('enrollments as e', 'e.id', '=', 'lp.enrollment_id')
                ->where('e.user_id', $userId)
                ->where('l.lesson_type', 'video')
                ->where('lp.status', 'completed')
                ->whereBetween(DB::raw('DATE(COALESCE(lp.completed_at, lp.updated_at))'), [$startDate, $endDate])
                ->pluck(DB::raw('DISTINCT DATE(COALESCE(lp.completed_at, lp.updated_at)) as date'))
                ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
                ->toArray();
        }

        $level1 = (int) config('report.heatmap_level_1_seconds', 900);
        $level2 = (int) config('report.heatmap_level_2_seconds', 2700);

        $heatmap = [];
        foreach ($dailyActivityMap as $date => $totalTime) {
            $intensity = 0;
            $hasQualifyingStreak = in_array($date, $qualifyingStreakDates, true);

            if ($hasQualifyingStreak) {
                if ($totalTime > 0 && $totalTime < $level1) {
                    $intensity = 1;
                } elseif ($totalTime >= $level1 && $totalTime <= $level2) {
                    $intensity = 2;
                } elseif ($totalTime > $level2) {
                    $intensity = 3;
                }
            }

            $heatmap[] = [
                'date' => $date,
                'total_time_seconds' => $totalTime,
                'intensity' => $intensity,
            ];
        }

        usort($heatmap, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return $heatmap;
    }

    public function getStreak(int $userId): array
    {
        $dates = [];

        if (Schema::hasTable('lesson_progress') && Schema::hasTable('lessons')) {
            $dates = DB::table('lesson_progress as lp')
                ->join('lessons as l', 'l.id', '=', 'lp.lesson_id')
                ->join('enrollments as e', 'e.id', '=', 'lp.enrollment_id')
                ->where('e.user_id', $userId)
                ->where('l.lesson_type', 'video')
                ->where('lp.status', 'completed')
                ->select(DB::raw('DISTINCT DATE(COALESCE(lp.completed_at, lp.last_accessed_at, lp.updated_at)) as date'))
                ->pluck('date')
                ->filter()
                ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
                ->toArray();
        }

        $dates = array_unique(array_filter($dates));
        rsort($dates);

        if (empty($dates)) {
            return [
                'current' => 0,
                'longest' => 0,
            ];
        }

        // Calculate Longest Streak
        $tempLongest = 1;
        $longest = 1;
        for ($i = 0; $i < count($dates) - 1; $i++) {
            $date1 = new \DateTime($dates[$i]);
            $date2 = new \DateTime($dates[$i + 1]);
            $diff = $date1->diff($date2)->days;

            if ($diff == 1) {
                $tempLongest++;
                if ($tempLongest > $longest) {
                    $longest = $tempLongest;
                }
            } else {
                $tempLongest = 1;
            }
        }

        // Calculate Current Streak
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $hasToday = in_array($today, $dates);
        $hasYesterday = in_array($yesterday, $dates);

        if (!$hasToday && !$hasYesterday) {
            $current = 0;
        } else {
            $currentStreakTemp = 0;
            $checkDate = $hasToday ? $today : $yesterday;

            while (in_array($checkDate, $dates)) {
                $currentStreakTemp++;
                $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
            }
            $current = $currentStreakTemp;
        }

        return [
            'current' => $current,
            'longest' => $longest,
        ];
    }

    public function getDailyMission(int $userId): array
    {
        $target = 2;

        $completedCount = DB::table('lesson_progress as lp')
            ->join('enrollments as e', 'e.id', '=', 'lp.enrollment_id')
            ->where('e.user_id', $userId)
            ->where('lp.status', 'completed')
            ->where(DB::raw('DATE(lp.completed_at)'), DB::raw('CURDATE()'))
            ->count();

        return [
            'title' => 'Hoàn thành 2 bài học',
            'target' => $target,
            'progress' => min($completedCount, $target),
            'is_completed' => $completedCount >= $target,
            'reward_xp' => 50,
        ];
    }
}