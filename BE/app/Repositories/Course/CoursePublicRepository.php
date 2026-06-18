<?php

namespace App\Repositories\Course;

use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;

class CoursePublicRepository
{
    public function findPublicCourseById(int $id): ?Course
    {
        return Course::where('id', $id)
            ->where('status', 'published')
            ->with(['categories' => function ($query) {
                $query->where('status', 'active');
            }])
            ->first();
    }

    public function getRelatedPublicCoursesBaseQuery(Course $currentCourse): Builder
    {
        return Course::query()
            ->where('status', 'published')
            ->where('id', '!=', $currentCourse->id)
            ->with(['categories' => function ($query) {
                $query->where('status', 'active');
            }, 'instructor'])
            ->withCount('reviews as rating_count')
            ->withAvg('reviews as rating_avg', 'rating');
    }
}
