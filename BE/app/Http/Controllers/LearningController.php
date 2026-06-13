<?php

namespace App\Http\Controllers;

use App\Http\Requests\Learning\MyCoursesRequest;
use App\Http\Resources\Learning\MyCourseResource;
use App\Services\LearningService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class LearningController extends Controller
{
    public function __construct(
        private readonly LearningService $learningService
    ) {
    }

    /**
     * Get the list of purchased courses for the authenticated learner.
     *
     * @param MyCoursesRequest $request
     * @return JsonResponse
     */
    public function myCourses(MyCoursesRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $enrollments = $this->learningService->getPurchasedCourses(
            $user,
            $request->validated()
        );

        return ApiResponse::paginated(
            MyCourseResource::collection($enrollments),
            $enrollments,
            'Lấy danh sách khóa học đã mua thành công.'
        );
    }
}
