<?php

namespace App\Repositories\Instructor;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InstructorQuizRepository
{
    public function paginateOwnedQuizzes(int $instructorId, array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 100);

        $query = Quiz::query()
            ->with([
                'course:id,instructor_id,title,status',
                'lesson:id,course_id,title,status',
                'questions.options',
            ])
            
            ->whereHas('course', function ($builder) use ($instructorId): void {
                $builder
                    ->where('instructor_id', $instructorId)
                    ;
            });

        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['lesson_id'])) {
            $query->where('lesson_id', (int) $filters['lesson_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function findOwnedQuiz(int $instructorId, int $quizId): ?Quiz
    {
        return Quiz::query()
            ->with([
                'course:id,instructor_id,title,status',
                'lesson:id,course_id,title,status',
                'questions.options',
            ])
            ->whereKey($quizId)
            
            ->whereHas('course', function ($builder) use ($instructorId): void {
                $builder
                    ->where('instructor_id', $instructorId)
                    ;
            })
            ->first();
    }

    public function findLessonWithCourse(int $lessonId): ?Lesson
    {
        return Lesson::query()
            ->with('course')
            ->whereKey($lessonId)
            
            ->first();
    }
}