<?php

namespace App\Services\Catalog;

use App\Repositories\Catalog\BannerRepository;
use App\Repositories\Catalog\CatalogCourseRepository;
use App\Repositories\Catalog\CategoryRepository;
use App\Repositories\Catalog\FeaturedInstructorRepository;
use Illuminate\Support\Collection;

class CatalogService
{
    public function __construct(
        private readonly BannerRepository $bannerRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly CatalogCourseRepository $courseRepository,
        private readonly FeaturedInstructorRepository $featuredInstructorRepository,
    ) {
    }

    public function home(array $filters): array
    {
        $now = now();

        $faqs = \App\Models\Faq::query()
            ->where('status', 'active')
            
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $testimonials = \App\Models\CourseReview::query()
            ->with(['order.user:id,full_name,avatar_url'])
            ->where('rating', '>=', 4)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            
            ->orderByDesc('rating')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $vouchers = \App\Models\Coupon::query()
            ->where('status', 'active')
            
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $stats = [
            'total_courses' => \App\Models\Course::query()->where('status', 'published')->count(),
            'total_students' => \App\Models\User::query()->where('role', 'learner')->count(),
            'total_instructors' => \App\Models\User::query()->where('role', 'instructor')->count(),
            'total_reviews' => \App\Models\CourseReview::query()->count(),
        ];

        return [
            'banners' => $this->bannerRepository->getActiveHomeBanners(),

            'categories' => $this->categoryRepository->getActiveForHome(),

            'featured_courses' => $this->courseRepository->featured([
                'page' => 1,
                'per_page' => 5,
            ]),

            'latest_courses' => $this->courseRepository->latest([
                'page' => 1,
                'per_page' => 5,
            ]),

            'discounted_courses' => $this->courseRepository->discounted([
                'page' => 1,
                'per_page' => 5,
            ]),

            'featured_instructors' => $this->featuredInstructorRepository->paginateFeatured(8),

            'faqs' => $faqs,

            'testimonials' => $testimonials,

            'vouchers' => $vouchers,

            'stats' => $stats,
        ];
    }

    public function categories(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->categoryRepository->paginateActive($perPage);
    }

    public function searchCourses(array $filters)
    {
        return $this->courseRepository->search($filters);
    }

    public function featuredCourses(array $filters)
    {
        return $this->courseRepository->featured($filters);
    }

    public function latestCourses(array $filters)
    {
        return $this->courseRepository->latest($filters);
    }

    public function featuredInstructors(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return $this->featuredInstructorRepository->paginateFeatured($perPage);
    }

    public function suggestions(array $filters): Collection
    {
        $keyword = trim((string) ($filters['q'] ?? $filters['query'] ?? $filters['search'] ?? $filters['keyword'] ?? ''));
        $limit = min((int) ($filters['limit'] ?? 10), 20);

        return $this->courseRepository->suggestions($keyword, $limit);
    }
}
