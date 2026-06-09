<?php
namespace App\Http\Controllers;
use App\Http\Requests\Catalog\CatalogListRequest;
// use App\Http\Requests\Catalog\CourseSearchRequest;
// use App\Http\Requests\Catalog\CourseSortRequest;
// use App\Http\Requests\Catalog\SearchSuggestionRequest;
// use App\Http\Resources\Catalog\CatalogCourseResource;
// use App\Http\Resources\Catalog\CategoryResource;
// use App\Http\Resources\Catalog\FeaturedInstructorResource;
use App\Http\Resources\Catalog\HomeResource;
// use App\Http\Resources\Catalog\SearchSuggestionResource;
use App\Services\Catalog\CatalogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
class CatalogController extends Controller
{
    public function __construct(private readonly CatalogService $catalogService)
    {
    }

    public function home(CatalogListRequest $request): JsonResponse
    {
        $homeData = $this->catalogService->home($request->validated(), $request->user());

        return ApiResponse::success(new HomeResource($homeData));
    }
}
