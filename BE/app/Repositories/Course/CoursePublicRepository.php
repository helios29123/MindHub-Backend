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

        $categoryFilters = [];
        if (!empty($filters['categories'])) {
            $cats = is_array($filters['categories']) ? $filters['categories'] : explode(',', $filters['categories']);
            $categoryFilters = array_merge($categoryFilters, $cats);
        }
        if (!empty($filters['category_slug'])) {
            $categoryFilters[] = $filters['category_slug'];
        }
        if (!empty($filters['category'])) {
            $categoryFilters[] = $filters['category'];
        }
        if (!empty($filters['category_id'])) {
            $categoryFilters[] = $filters['category_id'];
        }

        $categoryFilters = array_unique(array_filter(array_map('trim', $categoryFilters)));

        if (!empty($categoryFilters)) {
            $matchingCatIds = \App\Models\Category::where(function ($q) use ($categoryFilters) {
                $numericIds = array_filter($categoryFilters, 'is_numeric');
                if (!empty($numericIds)) {
                    $q->whereIn('id', $numericIds);
                }
                $q->orWhereIn('slug', $categoryFilters)
                  ->orWhereIn('name', $categoryFilters);
            })->pluck('id')->toArray();

            if (!empty($matchingCatIds)) {
                $childIds = \App\Models\Category::whereIn('parent_id', $matchingCatIds)->pluck('id')->toArray();
                $allCatIds = array_unique(array_merge($matchingCatIds, $childIds));

                $query->whereHas('categories', function ($q) use ($allCatIds) {
                    $q->whereIn('categories.id', $allCatIds);
                });
            } else {
                $query->whereHas('categories', function ($q) use ($categoryFilters) {
                    $q->where(function ($sub) use ($categoryFilters) {
                        $sub->whereIn('categories.name', $categoryFilters)
                            ->orWhereIn('categories.slug', $categoryFilters);
                    });
                });
            }
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
