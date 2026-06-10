<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\Marketing\CourseAnnouncementRequest;
use App\Http\Requests\Marketing\BannerRequest;
use App\Http\Resources\CourseAnnouncementResource;
use App\Http\Resources\BannerResource;
use App\Models\Course;
use App\Services\MarketingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MarketingController extends Controller
{
    public function __construct(
        private readonly MarketingService $marketingService
    ) {
    }

    public function courseAnnouncements(CourseAnnouncementRequest $request): JsonResponse
    {
        $courseId = (int) $request->validated()['course_id'];
        $course = Course::find($courseId);

        if (!$course) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        // Verify ownership
        if ((int) $course->instructor_id !== (int) $request->user()->id) {
            throw new BusinessException('Bạn không có quyền thực hiện thao tác này.', 403);
        }

        // Return HTTP 501 as required by specifications
        return response()->json([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => (new CourseAnnouncementResource(null))->resolve(),
        ], 501);
    }

    public function banners(Request $request, mixed $id = null): JsonResponse
    {
        // 1. Validate path parameter ID if present
        if ($id !== null) {
            $pathValidator = Validator::make(['id' => $id], [
                'id' => 'required|integer|min:1',
            ]);

            if ($pathValidator->fails()) {
                return ApiResponse::error('Dữ liệu không hợp lệ.', $pathValidator->errors()->toArray(), 422);
            }
            $id = (int) $id;
        }

        // 2. Handle GET for single item details
        if ($request->isMethod('get') && $id !== null) {
            $banner = $this->marketingService->getBanner($id);
            return ApiResponse::success(
                new BannerResource($banner),
                'Thao tác thành công',
                200
            );
        }

        // 3. Handle GET for listing paginated
        if ($request->isMethod('get')) {
            $queryValidator = Validator::make($request->query(), [
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            if ($queryValidator->fails()) {
                return ApiResponse::error('Dữ liệu không hợp lệ.', $queryValidator->errors()->toArray(), 422);
            }

            $banners = $this->marketingService->getBanners($queryValidator->validated());
            return ApiResponse::paginated(
                BannerResource::collection($banners),
                $banners,
                'Thao tác thành công'
            );
        }

        // 4. Handle POST for creating
        if ($request->isMethod('post')) {
            $bannerRequest = app(BannerRequest::class);
            $validator = Validator::make($request->all(), $bannerRequest->rules(), $bannerRequest->messages());

            if ($validator->fails()) {
                $errors = $validator->errors();
                $message = 'Dữ liệu không hợp lệ.';
                if ($errors->has('status')) {
                    $message = 'Trạng thái banner không hợp lệ.';
                } elseif ($errors->has('end_at')) {
                    $message = 'Thời gian banner không hợp lệ.';
                }
                return ApiResponse::error($message, $errors->toArray(), 422);
            }

            $banner = $this->marketingService->createBanner($validator->validated());
            return ApiResponse::success(
                json_encode(['banner_id' => $banner->id, 'status' => $banner->status]),
                'Thao tác thành công',
                200
            );
        }

        // 5. Handle PUT/PATCH for updating
        if ($request->isMethod('put') || $request->isMethod('patch')) {
            $bannerRequest = app(BannerRequest::class);
            $validator = Validator::make($request->all(), $bannerRequest->rules(), $bannerRequest->messages());

            if ($validator->fails()) {
                $errors = $validator->errors();
                $message = 'Dữ liệu không hợp lệ.';
                if ($errors->has('status')) {
                    $message = 'Trạng thái banner không hợp lệ.';
                } elseif ($errors->has('end_at')) {
                    $message = 'Thời gian banner không hợp lệ.';
                }
                return ApiResponse::error($message, $errors->toArray(), 422);
            }

            $banner = $this->marketingService->updateBanner($id, $validator->validated());
            return ApiResponse::success(
                json_encode(['banner_id' => $banner->id, 'status' => $banner->status]),
                'Thao tác thành công',
                200
            );
        }

        // 6. Handle DELETE for destroying
        if ($request->isMethod('delete') && $id !== null) {
            $this->marketingService->deleteBanner($id);
            return ApiResponse::success(
                null,
                'Thao tác thành công',
                200
            );
        }

        return ApiResponse::error('Phương thức không được hỗ trợ.', [], 405);
    }
}
