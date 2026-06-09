<?php

namespace App\Services\Instructor;

use App\Models\Course;
use App\Models\User;
use App\Repositories\Instructor\InstructorCourseRepository;
use Illuminate\Support\Facades\DB;

final class InstructorCourseService
{
    public function __construct(
        private readonly InstructorCourseRepository $instructorCourseRepository
    ) {
    }

    public function createCourse(User $instructor, array $validatedData): Course
    {
        return DB::transaction(function () use ($instructor, $validatedData): Course {
            $categoryIds = $validatedData['category_ids'] ?? [];

            unset($validatedData['category_ids']);

            $courseData = array_merge($validatedData, [
                'instructor_id' => $instructor->id,
                'status' => 'draft',
                'is_featured' => false,
                'total_duration_seconds' => 0,
                'published_at' => null,
                'admin_reject_reason' => null,
                'language' => $validatedData['language'] ?? 'vi',
                'level' => $validatedData['level'] ?? 'beginner',
            ]);

            $course = $this->instructorCourseRepository->create($courseData);

            if (!empty($categoryIds)) {
                $this->instructorCourseRepository->syncCategories($course, $categoryIds);
            }

            return $this->instructorCourseRepository->findWithCategories($course->id);
        });
    }
}