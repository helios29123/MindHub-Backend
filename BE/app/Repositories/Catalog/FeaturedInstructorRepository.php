<?php

namespace App\Repositories\Catalog;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeaturedInstructorRepository
{
    public function paginateFeatured(int $perPage): LengthAwarePaginator
    {
        return $this->featuredQuery()->paginate($perPage);
    }

    public function featured(int $limit): Collection
    {
        return $this->featuredQuery()->limit($limit)->get();
    }

    private function featuredQuery(): Builder
    {
        return User::query()
            ->select('users.*')
            ->where('users.role', 'instructor')
            ->where('users.status', 'active')
            ->with('instructorProfile')
            ->withCount([
                'publishedCourses as published_courses_count',
                'courseEnrollments as total_enrollments_count',
            ])
            ->selectSub(function ($query) {
                $query->from('course_reviews')
                    ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
                    ->join('courses', 'courses.id', '=', 'orders.course_id')
                    ->whereColumn('courses.instructor_id', 'users.id')
                    ->where('courses.status', 'published')
                    ->whereNull('course_reviews.deleted_at')
                    ->select(DB::raw('AVG(course_reviews.rating)'));
            }, 'average_rating')
            ->having('published_courses_count', '>', 0)
            ->orderByDesc('average_rating')
            ->orderByDesc('total_enrollments_count')
            ->orderByDesc('published_courses_count');
    }
}
