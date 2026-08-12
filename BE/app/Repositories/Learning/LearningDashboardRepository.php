<?php

namespace App\Repositories\Learning;

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

        $totalLearningSeconds = DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->sum('learning_duration_seconds');
        
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
            ->where('e.status', 'active')
            ->join('courses as c', 'c.id', '=', 'e.course_id')
            ->leftJoin('course_categories as cc', 'cc.course_id', '=', 'c.id')
            ->leftJoin('categories as cat', 'cat.id', '=', 'cc.category_id')
            ->orderByRaw('COALESCE(e.last_accessed_at, e.created_at) DESC')
            ->select([
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
            ->where('lp.user_id', $userId)
            ->where('l.course_id', $enrollment->course_id)
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
            'course_id' => $enrollment->course_id,
            'title' => $enrollment->title,
            'thumbnail_url' => $enrollment->thumbnail_url,
            'category_name' => $enrollment->category_name,
            'progress_percent' => (float)$enrollment->progress_percent,
            'current_lesson' => $currentLessonData
        ];
    }
    public function getHeatmap(int $userId, int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $records = DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->whereBetween(DB::raw('DATE(updated_at)'), [$startDate, $endDate])
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('SUM(learning_duration_seconds) as total_time_seconds')
            )
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->get();

        $heatmap = [];
        foreach ($records as $record) {
            $totalTime = (int) $record->total_time_seconds;
            $intensity = 0;
            if ($totalTime > 0 && $totalTime < 900) {
                $intensity = 1;
            } elseif ($totalTime >= 900 && $totalTime < 2700) {
                $intensity = 2;
            } elseif ($totalTime >= 2700) {
                $intensity = 3;
            }

            $heatmap[] = [
                'date' => $record->date,
                'total_time_seconds' => $totalTime,
                'intensity' => $intensity,
            ];
        }

        return $heatmap;
    }

    public function getStreak(int $userId): array
    {
        $dates = DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->where('learning_duration_seconds', '>', 0)
            ->select(DB::raw('DATE(updated_at) as date'))
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->toArray();

        $current = 0;
        $longest = 0;

        if (empty($dates)) {
            return ['current' => $current, 'longest' => $longest];
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
        $target = 2; // Hardcoded for now based on requirement
        
        $completedCount = DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->where(DB::raw('DATE(completed_at)'), DB::raw('CURDATE()'))
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