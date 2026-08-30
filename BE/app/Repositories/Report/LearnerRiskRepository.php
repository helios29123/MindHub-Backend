<?php

namespace App\Repositories\Report;

use App\Models\Enrollment;
use App\Models\Course;
use App\Models\LessonProgress;

class LearnerRiskRepository
{
    public function getCourseForInstructor(int $courseId, int $instructorId): ?Course
    {
        return Course::where('id', $courseId)
            
            ->first();
    }

    public function getEnrollmentsForCourse(int $courseId): \Illuminate\Database\Eloquent\Collection
    {
        return Enrollment::where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed', 'paused', 'expired'])
            ->with(['user'])
            ->get();
    }

    public function getLessonProgressCountForCourse(int $courseId): \Illuminate\Support\Collection
    {
        return LessonProgress::query()
            ->join('enrollments', 'enrollments.id', '=', 'lesson_progress.enrollment_id')
            ->whereHas('lesson', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->selectRaw('enrollments.user_id, count(*) as count')
            ->groupBy('enrollments.user_id')
            ->get()
            ->keyBy('user_id');
    }
}
