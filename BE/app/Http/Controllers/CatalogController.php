<?php

namespace App\Http\Controllers;

use App\Http\Requests\Catalog\CatalogListRequest;
use App\Http\Requests\Catalog\CourseSearchRequest;
use App\Http\Requests\Catalog\CourseSortRequest;
use App\Http\Requests\Catalog\SearchSuggestionRequest;
use App\Http\Resources\Catalog\CatalogCourseResource;
use App\Http\Resources\Catalog\CategoryResource;
use App\Http\Resources\Catalog\FeaturedInstructorResource;
use App\Http\Resources\Catalog\HomeResource;
use App\Http\Resources\Catalog\SearchSuggestionResource;
use App\Services\Catalog\CatalogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService
    ) {
    }

    public function home(CatalogListRequest $request): JsonResponse
    {
        $homeData = $this->catalogService->home(
            $request->validated()
        );

        return ApiResponse::success(
            new HomeResource($homeData),
            'Lấy dữ liệu thành công'
        );
    }

    public function categories(CatalogListRequest $request): JsonResponse
    {
        $categories = $this->catalogService->categories(
            $request->validated()
        );

        return ApiResponse::paginated(
            CategoryResource::collection($categories),
            $categories
        );
    }

    public function searchCourses(CourseSearchRequest $request): JsonResponse
    {
        $courses = $this->catalogService->searchCourses(
            $request->validated()
        );

        return ApiResponse::paginated(
            CatalogCourseResource::collection($courses),
            $courses
        );
    }

    public function sortCourses(CourseSortRequest $request): JsonResponse
    {
        $courses = $this->catalogService->searchCourses(
            $request->validated()
        );

        return ApiResponse::paginated(
            CatalogCourseResource::collection($courses),
            $courses
        );
    }

    public function featuredCourses(CatalogListRequest $request): JsonResponse
    {
        $courses = $this->catalogService->featuredCourses(
            $request->validated()
        );

        return ApiResponse::paginated(
            CatalogCourseResource::collection($courses),
            $courses
        );
    }

    public function latestCourses(CatalogListRequest $request): JsonResponse
    {
        $courses = $this->catalogService->latestCourses(
            $request->validated()
        );

        return ApiResponse::paginated(
            CatalogCourseResource::collection($courses),
            $courses
        );
    }

    public function featuredInstructors(CatalogListRequest $request): JsonResponse
    {
        $instructors = $this->catalogService->featuredInstructors(
            $request->validated()
        );

        return ApiResponse::paginated(
            FeaturedInstructorResource::collection($instructors),
            $instructors
        );
    }

    public function searchSuggestions(SearchSuggestionRequest $request): JsonResponse
    {
        $suggestions = $this->catalogService->suggestions(
            $request->validated()
        );

        return ApiResponse::success(
            SearchSuggestionResource::collection($suggestions),
            'Lấy dữ liệu thành công'
        );
    }
}
