<?php

namespace App\Repositories\Report;

use App\Models\Enrollment;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\LessonProgress;

class LearnerRiskRepository
{
    public function getCourseForInstructor(int $courseId, int $instructorId): ?Course
    {
        return Course::where('id', $courseId)
            ->whereNull('deleted_at')
            ->first();
    }

    public function getEnrollmentsForCourse(int $courseId): \Illuminate\Database\Eloquent\Collection
    {
        return Enrollment::where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed', 'paused', 'expired'])
            ->with(['user'])
            ->get();
    }

    public function getFailedQuizzesForCourse(int $courseId): \Illuminate\Support\Collection
    {
        // For risk analysis, failed quizzes for a user in a course 
        // We'll get all quiz attempts for the course to calculate risk per user.
        return QuizAttempt::whereHas('quiz.lesson', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->where('passed', false)
            ->get()
            ->groupBy('user_id');
    }

    public function getLessonProgressCountForCourse(int $courseId): \Illuminate\Support\Collection
    {
        return LessonProgress::whereHas('lesson', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->selectRaw('user_id, count(*) as count')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');
    }
}
