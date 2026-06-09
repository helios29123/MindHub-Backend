<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Http\Resources\CourseSectionResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\CourseReviewResource;
use App\Http\Resources\InstructorResource;
use App\Http\Resources\FaqResource;
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

    public function previewLesson(mixed $id): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Tham số không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $lesson = $this->coursePublicService->previewLesson((int) $id);

        $resource = new LessonResource($lesson);

        return ApiResponse::success($resource, 'Lấy bài học preview thành công');
    }

    public function reviews(mixed $id): JsonResponse
    {
        $input = array_merge(
            ['id' => $id],
            request()->only(['page', 'per_page', 'rating', 'sort'])
        );

        // Validate whitelist query parameters
        $allowedKeys = ['page', 'per_page', 'rating', 'sort'];
        $extraParams = array_diff(array_keys(request()->query()), $allowedKeys);

        if (!empty($extraParams)) {
            return ApiResponse::error('Tham số không hợp lệ.', ['query' => 'Chứa tham số không hợp lệ ngoài whitelist.'], 422);
        }

        $validator = Validator::make($input, [
            'id' => 'required|integer|min:1',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'rating' => 'nullable|integer|min:1|max:5',
            'sort' => 'nullable|string|in:newest,highest_rating,lowest_rating',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Tham số không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $result = $this->coursePublicService->reviews((int) $id, $validator->validated());

        return ApiResponse::paginated(
            CourseReviewResource::collection($result['paginator']),
            $result['paginator'],
            'Lấy danh sách đánh giá thành công'
        );
    }

    public function showInstructor(mixed $id): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Tham số không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $result = $this->coursePublicService->showInstructor((int) $id);

        $resource = new InstructorResource($result['instructor']);

        return ApiResponse::success($resource, 'Lấy thông tin giảng viên thành công');
    }

    public function faqs(mixed $id): JsonResponse
    {
        $input = array_merge(
            ['id' => $id],
            request()->only(['page', 'per_page'])
        );

        // Validate whitelist query parameters
        $allowedKeys = ['page', 'per_page'];
        $extraParams = array_diff(array_keys(request()->query()), $allowedKeys);

        if (!empty($extraParams)) {
            return ApiResponse::error('Tham số không hợp lệ.', ['query' => 'Chứa tham số không hợp lệ ngoài whitelist.'], 422);
        }

        $validator = Validator::make($input, [
            'id' => 'required|integer|min:1',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Tham số không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $result = $this->coursePublicService->faqs((int) $id, $validator->validated());

        return ApiResponse::paginated(
            FaqResource::collection($result['paginator']),
            $result['paginator'],
            'Lấy danh sách FAQ thành công'
        );
    }
}
