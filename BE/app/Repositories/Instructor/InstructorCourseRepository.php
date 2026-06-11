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
    }    public function findByIdWithReviewRelations(int $courseId): ?Course
    {
        return Course::query()
            ->with(['categories', 'sections.lessons'])
            ->find($courseId);
    }
    public function markAsPendingReview(Course $course): Course
    {
        $course->forceFill([
            'status' => 'pending_review',
            'admin_reject_reason' => null,
        ])->save();
        return $this->findByIdWithReviewRelations((int) $course->id)
            ?? $course->fresh(['categories', 'sections.lessons'])
            ?? $course;
    }
}