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

    public function searchPublicCourses(array $filters, int $perPage)
    {
        $query = Course::query()
            ->where('status', 'published')
            ->with(['categories' => function ($q) {
                $q->where('status', 'active');
            }, 'instructor'])
            ->withCount('reviews as rating_count')
            ->withAvg('reviews as rating_avg', 'rating');

        if (!empty($filters['query'])) {
            $search = '%' . strtolower($filters['query']) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', [$search])
                  ->orWhereHas('instructor', function ($q2) use ($search) {
                      $q2->whereRaw('LOWER(full_name) LIKE ?', [$search]);
                  });
            });
        }

        if (!empty($filters['categories'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->whereIn('name', $filters['categories']);
            });
        }

        if (isset($filters['minRating']) && $filters['minRating'] > 0) {
            $query->having('rating_avg', '>=', $filters['minRating']);
        }

        if (!empty($filters['priceType'])) {
            if ($filters['priceType'] === 'free') {
                $query->where(function($q) {
                    $q->where('price', 0)->orWhere('sale_price', 0);
                });
            } elseif ($filters['priceType'] === 'paid') {
                $query->where('price', '>', 0)->where(function($q) {
                    $q->whereNull('sale_price')->orWhere('sale_price', '>', 0);
                });
            }
        }

        $sortBy = $filters['sortBy'] ?? 'newest';
        switch ($sortBy) {
            case 'popular':
                // Assuming popular means highest enrollments or rating count, we use rating_count for now if enrolled_count doesn't exist
                $query->orderByDesc('rating_count');
                break;
            case 'highest-rated':
                $query->orderByDesc('rating_avg');
                break;
            case 'lowest-price':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'highest-price':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'newest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        return $query->paginate($perPage);
    }
}
