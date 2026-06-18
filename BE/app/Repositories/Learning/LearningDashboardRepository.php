<?php

namespace App\Repositories\Learning;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LearningDashboardRepository
{
    public function getUserEnrollments(int $userId): Collection
    {
        return DB::table('enrollments as e')
            ->join('courses as c', 'c.id', '=', 'e.course_id')
            ->where('e.user_id', $userId)
            ->select([
                'e.id as enrollment_id',
                'e.course_id',
                'e.status as enrollment_status',
                'e.progress_percent',
                'e.completed_at',
                'e.last_accessed_at',
                'e.enrolled_at',
                'c.title as course_title',
                'c.thumbnail_url as course_thumbnail_url',
                'c.status as course_status',
            ])
            ->orderByRaw('COALESCE(e.last_accessed_at, e.updated_at, e.created_at) DESC')
            ->get();
    }

    public function getRecentLessonProgress(int $userId, int $limit): Collection
    {
        return DB::table('lesson_progress as lp')
            ->join('lessons as l', 'l.id', '=', 'lp.lesson_id')
            ->join('courses as c', 'c.id', '=', 'l.course_id')
            ->join('enrollments as e', function ($join) use ($userId): void {
                $join->on('e.course_id', '=', 'l.course_id')
                    ->where('e.user_id', '=', $userId);
            })
            ->leftJoin('video_progress as vp', function ($join) use ($userId): void {
                $join->on('vp.lesson_id', '=', 'lp.lesson_id')
                    ->where('vp.user_id', '=', $userId);
            })
            ->where('lp.user_id', $userId)
            ->select([
                'e.id as enrollment_id',
                'e.course_id',
                'e.progress_percent',
                'c.title as course_title',
                'c.thumbnail_url as course_thumbnail_url',
                'l.id as lesson_id',
                'l.title as lesson_title',
                'l.lesson_type',
                'l.video_duration_seconds',
                'lp.status as lesson_status',
                'lp.learning_duration_seconds',
                'lp.last_accessed_at as lesson_last_accessed_at',
                'vp.current_second as video_current_second',
                DB::raw('COALESCE(lp.last_accessed_at, lp.updated_at, lp.created_at) as accessed_at'),
            ])
            ->orderByRaw('COALESCE(lp.last_accessed_at, lp.updated_at, lp.created_at) DESC')
            ->limit($limit)
            ->get();
    }

    public function getRecentEnrollmentFallback(int $userId, int $limit): Collection
    {
        return DB::table('enrollments as e')
            ->join('courses as c', 'c.id', '=', 'e.course_id')
            ->where('e.user_id', $userId)
            ->select([
                'e.id as enrollment_id',
                'e.course_id',
                'e.progress_percent',
                'e.status as enrollment_status',
                'e.last_accessed_at',
                'e.enrolled_at',
                'c.title as course_title',
                'c.thumbnail_url as course_thumbnail_url',
                DB::raw('COALESCE(e.last_accessed_at, e.updated_at, e.created_at) as accessed_at'),
            ])
            ->orderByRaw('COALESCE(e.last_accessed_at, e.updated_at, e.created_at) DESC')
            ->limit($limit)
            ->get();
    }
}