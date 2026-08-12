<?php

namespace App\Repositories\Quiz;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuizRepository
{
    public function findCourseById(int $courseId): ?Course
    {
        return Course::query()
            ->whereKey($courseId)
            ->whereNull('deleted_at')
            ->first();
    }

    public function findEnrollment(int $userId, int $courseId): ?Enrollment
    {
        return Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed'])
            ->first();
    }

    public function countPublishedLessons(int $courseId): int
    {
        return (int) DB::table('lessons')
            ->where('course_id', $courseId)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->count();
    }

    public function getPublishedQuizIds(int $courseId): Collection
    {
        return DB::table('quizzes')
            ->where('course_id', $courseId)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->pluck('id');
    }
}