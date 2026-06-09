<?php

namespace App\Repositories\Instructor;

use App\Models\Course;

final class InstructorCourseRepository
{
    public function create(array $courseData): Course
    {
        return Course::create($courseData);
    }

    public function syncCategories(Course $course, array $categoryIds): void
    {
        $course->categories()->sync($categoryIds);
    }

    public function findWithCategories(int $courseId): Course
    {
        return Course::query()
            ->with(['categories'])
            ->findOrFail($courseId);
    }
}