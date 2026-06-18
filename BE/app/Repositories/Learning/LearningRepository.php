<?php

namespace App\Repositories\Learning;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LearningRepository
{
    public function findCourseForLearningOverview(int $courseId): ?object
    {
        return DB::table('courses')
            ->where('id', $courseId)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->select([
                'id',
                'title',
                'thumbnail_url',
                'status',
                'total_duration_seconds',
            ])
            ->first();
    }

    public function findEnrollmentForUserCourse(int $userId, int $courseId): ?object
    {
        return DB::table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed'])
            ->select([
                'id',
                'user_id',
                'course_id',
                'order_id',
                'status',
                'progress_percent',
                'completed_at',
                'last_accessed_at',
                'enrolled_at',
                'created_at',
                'updated_at',
            ])
            ->first();
    }

    public function getPublishedLessonsWithProgress(int $userId, int $courseId): Collection
    {
        return DB::table('lessons as l')
            ->join('course_sections as cs', 'cs.id', '=', 'l.course_section_id')
            ->leftJoin('lesson_progress as lp', function ($join) use ($userId): void {
                $join->on('lp.lesson_id', '=', 'l.id')
                    ->where('lp.user_id', '=', $userId);
            })
            ->leftJoin('video_progress as vp', function ($join) use ($userId): void {
                $join->on('vp.lesson_id', '=', 'l.id')
                    ->where('vp.user_id', '=', $userId);
            })
            ->where('l.course_id', $courseId)
            ->where('l.status', 'published')
            ->whereNull('l.deleted_at')
            ->whereNull('cs.deleted_at')
            ->where('cs.status', 'published')
            ->select([
                'cs.id as section_id',
                'cs.title as section_title',
                'cs.sort_order as section_sort_order',
                'l.id as lesson_id',
                'l.title as lesson_title',
                'l.lesson_type',
                'l.video_duration_seconds',
                'l.is_preview',
                'l.status as lesson_status',
                'l.sort_order as lesson_sort_order',
                'lp.status as progress_status',
                'lp.started_at',
                'lp.completed_at as lesson_completed_at',
                'lp.learning_duration_seconds',
                'lp.last_accessed_at as lesson_last_accessed_at',
                'vp.current_second as video_current_second',
            ])
            ->orderBy('cs.sort_order')
            ->orderBy('l.sort_order')
            ->orderBy('l.id')
            ->get();
    }
}