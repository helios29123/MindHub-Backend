<?php

namespace App\Repositories\Catalog;

use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CatalogCourseRepository
{
    public function search(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = $this->publicCourseQuery();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $courseQuery) use ($search) {
                $courseQuery->where('courses.title', 'like', "%{$search}%")
                    ->orWhere('courses.short_description', 'like', "%{$search}%")
                    ->orWhere('courses.description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];

            $query->whereHas('categories', function (Builder $categoryQuery) use ($categoryId) {
                $categoryQuery->where('categories.id', $categoryId)
                    ->where('categories.status', 'active')
                    ->whereNull('categories.deleted_at');
            });
        }

        if (! empty($filters['category_slug'])) {
            $categorySlug = trim((string) $filters['category_slug']);

            $query->whereHas('categories', function (Builder $categoryQuery) use ($categorySlug) {
                $categoryQuery->where('categories.slug', $categorySlug)
                    ->where('categories.status', 'active')
                    ->whereNull('categories.deleted_at');
            });
        }

        if (! empty($filters['level'])) {
            $query->where('courses.level', $filters['level']);
        }

        if (! empty($filters['instructor_id'])) {
            $query->where('courses.instructor_id', (int) $filters['instructor_id']);
        }

        if (! empty($filters['language'])) {
            $query->where('courses.language', $filters['language']);
        }

        if (isset($filters['min_price'])) {
            $query->whereRaw('COALESCE(courses.sale_price, courses.price) >= ?', [
                $filters['min_price'],
            ]);
        }

        if (isset($filters['max_price'])) {
            $query->whereRaw('COALESCE(courses.sale_price, courses.price) <= ?', [
                $filters['max_price'],
            ]);
        }

        $this->applySort($query, $filters['sort'] ?? null);

        return $query->paginate($perPage);
    }

    public function featured(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $timeframe = now()->subDays(90);

        // Find global E_max in the last 90 days for normalization
        $eMax = \Illuminate\Support\Facades\DB::table('enrollments')
            ->where('enrolled_at', '>=', $timeframe)
            ->whereIn('status', ['active', 'completed'])
            ->selectRaw('COUNT(id) as e_count')
            ->groupBy('course_id')
            ->orderByDesc('e_count')
            ->value('e_count');
            
        $eMax = max((float)$eMax, 1.0);

        $query = $this->publicCourseQuery()
            ->selectSub(function ($query) use ($timeframe) {
                $query->from('enrollments')
                    ->whereColumn('enrollments.course_id', 'courses.id')
                    ->where('enrollments.enrolled_at', '>=', $timeframe)
                    ->whereIn('enrollments.status', ['active', 'completed'])
                    ->selectRaw('COUNT(enrollments.id)');
            }, 'recent_enrollments')
            ->selectSub(function ($query) use ($timeframe) {
                $query->from('enrollments')
                    ->whereColumn('enrollments.course_id', 'courses.id')
                    ->where('enrollments.enrolled_at', '>=', $timeframe)
                    ->whereIn('enrollments.status', ['active', 'completed'])
                    ->selectRaw('COALESCE(AVG(enrollments.progress_percent), 0)');
            }, 'recent_progress')
            ->orderByDesc('courses.is_featured')
            ->orderByRaw("
                IF(recent_enrollments >= 10,
                    (0.4 * (recent_enrollments / $eMax)) + 
                    (0.4 * (recent_progress / 100)) + 
                    (0.2 * (average_rating / 5)),
                    0
                ) DESC
            ")
            ->orderByDesc('enrollments_count')
            ->orderByDesc('average_rating')
            ->orderByDesc('courses.id');

        return $query->paginate($perPage);
    }

    public function latest(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = $this->publicCourseQuery()
            ->orderByDesc('courses.published_at')
            ->orderByDesc('courses.id');

        return $query->paginate($perPage);
    }

    public function discounted(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = $this->publicCourseQuery()
            ->whereNotNull('courses.sale_price')
            ->whereRaw('courses.sale_price < courses.price')
            ->orderByRaw('(courses.price - courses.sale_price) / courses.price DESC')
            ->orderByDesc('courses.published_at')
            ->orderByDesc('courses.id');

        return $query->paginate($perPage);
    }

    public function suggestions(string $keyword, int $limit = 10): Collection
    {
        $keyword = trim($keyword);
        $limit = min(max($limit, 1), 20);

        if ($keyword === '') {
            return collect();
        }

        $courseLimit = (int) ceil($limit / 2);
        $categoryLimit = $limit - $courseLimit;

        /*
         * Gợi ý khóa học:
         * Chỉ lấy khóa học published + instructor active + instructor không bị khóa.
         */
        $courses = DB::table('courses')
            ->select([
                'courses.id',
                DB::raw('courses.title as text'),
                'courses.slug',
                DB::raw("'course' as type"),
            ])
            ->where('courses.status', 'published')
            ->whereNull('courses.deleted_at')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('users')
                    ->whereColumn('users.id', 'courses.instructor_id')
                    ->where('users.status', 'active')
                    ->whereNull('users.deleted_at')
                    ->where(function ($userQuery) {
                        $userQuery->whereNull('users.locked')
                            ->orWhere('users.locked', 0);
                    });
            })
            ->where(function ($query) use ($keyword) {
                $query->where('courses.title', 'like', "%{$keyword}%")
                    ->orWhere('courses.short_description', 'like', "%{$keyword}%");
            })
            ->orderByDesc('courses.is_featured')
            ->orderByDesc('courses.published_at')
            ->limit($courseLimit)
            ->get();

        /*
         * Gợi ý danh mục:
         * Chỉ lấy danh mục active và có ít nhất 1 khóa học public hợp lệ.
         *
         * Có 2 trường hợp:
         * 1. Danh mục đó có khóa học trực tiếp.
         * 2. Danh mục cha có danh mục con đang có khóa học.
         */
        $categories = DB::table('categories')
            ->select([
                'categories.id',
                DB::raw('categories.name as text'),
                'categories.slug',
                DB::raw("'category' as type"),
            ])
            ->where('categories.status', 'active')
            ->whereNull('categories.deleted_at')
            ->where(function ($categoryQuery) {
                /*
                 * Trường hợp 1:
                 * Danh mục hiện tại có khóa học public trực tiếp.
                 */
                $categoryQuery->whereExists(function ($exists) {
                    $exists->selectRaw('1')
                        ->from('course_categories')
                        ->join('courses', 'courses.id', '=', 'course_categories.course_id')
                        ->join('users', 'users.id', '=', 'courses.instructor_id')
                        ->whereColumn('course_categories.category_id', 'categories.id')
                        ->where('courses.status', 'published')
                        ->whereNull('courses.deleted_at')
                        ->where('users.status', 'active')
                        ->whereNull('users.deleted_at')
                        ->where(function ($userQuery) {
                            $userQuery->whereNull('users.locked')
                                ->orWhere('users.locked', 0);
                        });
                })

                /*
                 * Trường hợp 2:
                 * Danh mục cha không có khóa trực tiếp nhưng danh mục con có khóa học public.
                 */
                ->orWhereExists(function ($exists) {
                    $exists->selectRaw('1')
                        ->from('categories as child_categories')
                        ->join('course_categories', 'course_categories.category_id', '=', 'child_categories.id')
                        ->join('courses', 'courses.id', '=', 'course_categories.course_id')
                        ->join('users', 'users.id', '=', 'courses.instructor_id')
                        ->whereColumn('child_categories.parent_id', 'categories.id')
                        ->where('child_categories.status', 'active')
                        ->whereNull('child_categories.deleted_at')
                        ->where('courses.status', 'published')
                        ->whereNull('courses.deleted_at')
                        ->where('users.status', 'active')
                        ->whereNull('users.deleted_at')
                        ->where(function ($userQuery) {
                            $userQuery->whereNull('users.locked')
                                ->orWhere('users.locked', 0);
                        });
                });
            })
            ->where(function ($query) use ($keyword) {
                $query->where('categories.name', 'like', "%{$keyword}%")
                    ->orWhere('categories.slug', 'like', "%{$keyword}%");
            })
            ->orderBy('categories.sort_order')
            ->orderByDesc('categories.id')
            ->limit($categoryLimit)
            ->get();

        return $courses
            ->merge($categories)
            ->take($limit)
            ->values();
    }

    private function publicCourseQuery()
    {
        return Course::query()
            ->with([
                'instructor:id,full_name',
                'categories:id,parent_id,name,slug,description,sort_order',
            ])
            ->where('courses.status', 'published')
            ->whereNull('courses.deleted_at')

            /*
             * Rule mới:
             * Instructor bị khóa/inactive thì course không hiển thị public.
             */
            ->whereHas('instructor', function (Builder $instructorQuery) {
                $instructorQuery->where('users.status', 'active')
                    ->whereNull('users.deleted_at')
                    ->where(function (Builder $lockedQuery) {
                        $lockedQuery->whereNull('users.locked')
                            ->orWhere('users.locked', 0);
                    });
            })

            ->select('courses.*')
            ->selectSub(function ($query) {
                $query->from('orders')
                    ->join('course_reviews', 'course_reviews.order_id', '=', 'orders.id')
                    ->whereColumn('orders.course_id', 'courses.id')
                    ->whereNull('course_reviews.deleted_at')
                    ->selectRaw('COALESCE(AVG(course_reviews.rating), 0)');
            }, 'average_rating')
            ->selectSub(function ($query) {
                $query->from('orders')
                    ->join('course_reviews', 'course_reviews.order_id', '=', 'orders.id')
                    ->whereColumn('orders.course_id', 'courses.id')
                    ->whereNull('course_reviews.deleted_at')
                    ->selectRaw('COUNT(course_reviews.id)');
            }, 'reviews_count')
            ->selectSub(function ($query) {
                $query->from('enrollments')
                    ->whereColumn('enrollments.course_id', 'courses.id')
                    ->whereIn('enrollments.status', ['active', 'completed'])
                    ->selectRaw('COUNT(enrollments.id)');
            }, 'enrollments_count');
    }

    private function applySort(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'latest' => $query
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'price_asc' => $query
                ->orderByRaw('COALESCE(courses.sale_price, courses.price) ASC')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'price_desc' => $query
                ->orderByRaw('COALESCE(courses.sale_price, courses.price) DESC')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'rating_desc' => $query
                ->orderByRaw('average_rating DESC')
                ->orderByRaw('reviews_count DESC')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'best_selling' => $query
                ->orderByRaw('enrollments_count DESC')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'featured' => $query
                ->orderByDesc('courses.is_featured')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            default => $query
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),
        };
    }
}
