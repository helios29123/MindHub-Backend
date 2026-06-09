<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Http\Resources\CourseSectionResource;
use App\Services\CoursePublicService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CoursePublicController extends Controller
{
    public function __construct(
        private readonly CoursePublicService $coursePublicService
    ) {
    }

    public function show(string $slug): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['slug' => $slug], [
            'slug' => 'required|string|min:1|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->coursePublicService->show($slug);

        $resource = (new CourseResource($result['course']))->additional([
            'is_enrolled' => $result['is_enrolled'],
            'enrollment_status' => $result['enrollment_status'],
            'is_in_wishlist' => $result['is_in_wishlist'],
            'has_access' => $result['has_access'],
        ]);

        return ApiResponse::success($resource, 'Lấy chi tiết khóa học thành công');
    }

    public function outline(mixed $id): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->coursePublicService->outline((int) $id);

        $resource = CourseSectionResource::collection($result['sections']);
        $resource->collection->each(function ($secResource) use ($result) {
            $secResource->additional(['has_access' => $result['has_access']]);
        });

        return ApiResponse::success($resource, 'Lấy lộ trình khóa học thành công');
    }
}
