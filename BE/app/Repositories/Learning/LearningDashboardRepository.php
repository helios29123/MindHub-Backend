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
}