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
        $timeframe = now()->subDays(90);

        // Find global TE_max in the last 90 days for normalization
        $teMax = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.enrolled_at', '>=', $timeframe)
            ->whereIn('enrollments.status', ['active', 'completed'])
            ->selectRaw('COUNT(enrollments.id) as te_count')
            ->groupBy('courses.instructor_id')
            ->orderByDesc('te_count')
            ->value('te_count');
            
        $teMax = max((float)$teMax, 1.0);

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
                    ->select(DB::raw('AVG(course_reviews.rating)'));
            }, 'average_rating')
            ->selectSub(function ($query) use ($timeframe) {
                $query->from('enrollments')
                    ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                    ->whereColumn('courses.instructor_id', 'users.id')
                    ->where('enrollments.enrolled_at', '>=', $timeframe)
                    ->whereIn('enrollments.status', ['active', 'completed'])
                    ->selectRaw('COUNT(enrollments.id)');
            }, 'recent_enrollments')
            ->selectSub(function ($query) use ($timeframe) {
                $query->from('enrollments')
                    ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                    ->whereColumn('courses.instructor_id', 'users.id')
                    ->where('enrollments.enrolled_at', '>=', $timeframe)
                    ->whereIn('enrollments.status', ['active', 'completed'])
                    ->selectRaw('COALESCE(AVG(enrollments.progress_percent), 0)');
            }, 'recent_progress')
            ->having('published_courses_count', '>', 0)
            ->orderByRaw("((0.6 * (recent_enrollments / $teMax)) + (0.4 * (recent_progress / 100))) DESC")
            ->orderByDesc('average_rating')
            ->orderByDesc('total_enrollments_count')
            ->orderByDesc('published_courses_count');
    }
}

