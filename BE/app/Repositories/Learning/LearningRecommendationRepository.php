<?php

namespace App\Repositories\Learning;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Builder;

class LearningRecommendationRepository
{
    public function getLearnerWishlistCourseIds(int $learnerId): array
    {
        return Wishlist::where('user_id', $learnerId)
            ->pluck('course_id')
            ->toArray();
    }

    public function getLearnerEnrolledCourseIds(int $learnerId): array
    {
        return Enrollment::where('user_id', $learnerId)
            ->pluck('course_id')
            ->toArray();
    }

    public function getPreferenceCategoryIds(array $courseIds): array
    {
        if (empty($courseIds)) {
            return [];
        }

        return \Illuminate\Support\Facades\DB::table('course_categories')
            ->whereIn('course_id', $courseIds)
            ->distinct()
            ->pluck('category_id')
            ->toArray();
    }

    public function getCourseLevels(array $courseIds): array
    {
        if (empty($courseIds)) {
            return [];
        }

        return Course::whereIn('id', $courseIds)
            ->whereNotNull('course_level')
            ->distinct()
            ->pluck('course_level')
            ->toArray();
    }

    public function getCandidatePublishedCourses(array $excludeCourseIds): Builder
    {
        $query = Course::query()
            ->where('status', 'published')
            ->with(['categories' => function ($query) {
                $query->where('status', 'active');
            }]);

        if (!empty($excludeCourseIds)) {
            $query->whereNotIn('id', $excludeCourseIds);
        }

        return $query;
    }

    public function getLearnerEnrollmentsWithCourse(int $learnerId): \Illuminate\Database\Eloquent\Collection
    {
        return Enrollment::where('user_id', $learnerId)
            ->with(['course.categories'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
