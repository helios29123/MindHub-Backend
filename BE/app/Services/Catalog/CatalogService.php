<?php

namespace App\Services\Catalog;

use App\Repositories\Catalog\BannerRepository;
use App\Repositories\Catalog\CatalogCourseRepository;
use App\Repositories\Catalog\CategoryRepository;
use App\Repositories\Catalog\FeaturedInstructorRepository;

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
        return [
            'banners' => $this->bannerRepository->getActiveHomeBanners(),

            'categories' => $this->categoryRepository->getActiveForHome(),

            'featured_courses' => $this->courseRepository->featured([
                'page' => 1,
                'per_page' => 8,
            ]),

            'latest_courses' => $this->courseRepository->latest([
                'page' => 1,
                'per_page' => 8,
            ]),

            'featured_instructors' => $this->featuredInstructorRepository->paginateFeatured(8),
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
}
