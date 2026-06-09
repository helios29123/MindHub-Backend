<?php
namespace App\Services\Catalog;
use App\Models\User;
use App\Repositories\Catalog\BannerRepository;
use App\Repositories\Catalog\CatalogCourseRepository;
use App\Repositories\Catalog\CategoryRepository;
use App\Repositories\Catalog\FeaturedInstructorRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CatalogService
{
    private const DEFAULT_PER_PAGE = 10;
    private const HOME_LIMIT = 8;

    public function __construct(
        private readonly BannerRepository $bannerRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly CatalogCourseRepository $courseRepository,
        private readonly FeaturedInstructorRepository $featuredInstructorRepository,
    ) {
    }

    public function home(array $filters, ?User $currentUser = null): array
    {
        $limit = min((int) ($filters['per_page'] ?? self::HOME_LIMIT), 20);

        return [
            'banners' => $this->bannerRepository->activeHomeBanners(),
            'categories' => $this->categoryRepository->activeForHome($limit),
            'featured_courses' => $this->courseRepository->featured($limit, $currentUser),
            'latest_courses' => $this->courseRepository->latest($limit, $currentUser),
            'featured_instructors' => $this->featuredInstructorRepository->featured($limit),
        ];
    }

    public function categories(array $filters): LengthAwarePaginator
    {
        return $this->categoryRepository->paginateActive($this->perPage($filters));
    }

    public function searchCourses(array $filters, ?User $currentUser = null): LengthAwarePaginator
    {
        return $this->courseRepository->paginatePublic($filters, $this->perPage($filters), $currentUser);
    }

    public function sortCourses(array $filters, ?User $currentUser = null): LengthAwarePaginator
    {
        return $this->courseRepository->paginatePublic($filters, $this->perPage($filters), $currentUser);
    }

    public function featuredCourses(array $filters, ?User $currentUser = null): LengthAwarePaginator
    {
        return $this->courseRepository->paginateFeatured($this->perPage($filters), $currentUser);
    }

    public function latestCourses(array $filters, ?User $currentUser = null): LengthAwarePaginator
    {
        return $this->courseRepository->paginateLatest($this->perPage($filters), $currentUser);
    }

    public function featuredInstructors(array $filters): LengthAwarePaginator
    {
        return $this->featuredInstructorRepository->paginateFeatured($this->perPage($filters));
    }

    public function suggestions(array $filters): Collection
    {
        return $this->courseRepository->suggestions(
            trim((string) ($filters['q'] ?? '')),
            min((int) ($filters['limit'] ?? 10), 20)
        );
    }

    private function perPage(array $filters): int
    {
        return min((int) ($filters['per_page'] ?? self::DEFAULT_PER_PAGE), 50);
    }
}
