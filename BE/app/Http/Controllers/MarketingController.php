<?php
namespace App\Http\Controllers;
use App\Exceptions\BusinessException;
use App\Http\Requests\Marketing\BannerRequest;
use App\Http\Requests\Marketing\CourseAnnouncementRequest;
use App\Http\Requests\Marketing\InstructorCouponCourseOptionRequest;
use App\Http\Requests\Marketing\InstructorCouponIndexRequest;
use App\Http\Requests\Marketing\InstructorCouponStatusRequest;
use App\Http\Requests\Marketing\InstructorCouponStoreRequest;
use App\Http\Requests\Marketing\InstructorCouponSummaryRequest;
use App\Http\Requests\Marketing\InstructorCouponUpdateRequest;
use App\Http\Resources\Admin\BannerResource;
use App\Http\Resources\Marketing\CourseAnnouncementResource;
use App\Http\Resources\Marketing\InstructorCouponCourseOptionResource;
use App\Http\Resources\Marketing\InstructorCouponDeleteResource;
use App\Http\Resources\Marketing\InstructorCouponDetailResource;
use App\Http\Resources\Marketing\InstructorCouponResource;
use App\Http\Resources\Marketing\InstructorCouponSummaryResource;
use App\Models\Course;
use App\Services\Marketing\InstructorCouponService;
use App\Services\Marketing\MarketingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class MarketingController extends Controller
{
    public function __construct(
        private readonly MarketingService $marketingService,
        private readonly InstructorCouponService $instructorCouponService
    ) {
    }
    public function courseAnnouncements(CourseAnnouncementRequest $request): JsonResponse
    {
        $courseId = (int) $request->validated()['course_id'];
        $course = Course::find($courseId);
        if (!$course) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }
        if ((int) $course->instructor_id !== (int) $request->user()->id) {
            throw new BusinessException('Bạn không có quyền thực hiện thao tác này.', 403);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => (new CourseAnnouncementResource(null))->resolve(),
        ], 501);
    }
    public function instructorCouponSummary(InstructorCouponSummaryRequest $request): JsonResponse
    {
        $summary = $this->instructorCouponService->getSummary(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Lấy tổng quan mã giảm giá thành công.',
            'data' => (new InstructorCouponSummaryResource($summary))->resolve($request),
        ]);
    }
    public function instructorCoupons(InstructorCouponIndexRequest $request): JsonResponse
    {
        $coupons = $this->instructorCouponService->paginateCoupons(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách mã giảm giá thành công.',
            'data' => InstructorCouponResource::collection($coupons->items())->resolve($request),
            'meta' => [
                'current_page' => $coupons->currentPage(),
                'last_page' => $coupons->lastPage(),
                'per_page' => $coupons->perPage(),
                'total' => $coupons->total(),
            ],
        ]);
    }
    public function storeInstructorCoupon(InstructorCouponStoreRequest $request): JsonResponse
    {
        $coupon = $this->instructorCouponService->createCoupon(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Tạo mã giảm giá thành công.',
            'data' => (new InstructorCouponResource($coupon))->resolve($request),
        ], 201);
    }
    public function showInstructorCoupon(Request $request, int $id): JsonResponse
    {
        $coupon = $this->instructorCouponService->showCoupon($request->user(), $id);
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết mã giảm giá thành công.',
            'data' => (new InstructorCouponDetailResource($coupon))->resolve($request),
        ]);
    }
    public function updateInstructorCoupon(InstructorCouponUpdateRequest $request, int $id): JsonResponse
    {
        $coupon = $this->instructorCouponService->updateCoupon(
            $request->user(),
            $id,
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật mã giảm giá thành công.',
            'data' => (new InstructorCouponResource($coupon))->resolve($request),
        ]);
    }
    public function updateInstructorCouponStatus(InstructorCouponStatusRequest $request, int $id): JsonResponse
    {
        $coupon = $this->instructorCouponService->updateCouponStatus(
            $request->user(),
            $id,
            (string) $request->validated('status')
        );
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái mã giảm giá thành công.',
            'data' => (new InstructorCouponResource($coupon))->resolve($request),
        ]);
    }
    public function destroyInstructorCoupon(Request $request, int $id): JsonResponse
    {
        $result = $this->instructorCouponService->deleteCoupon($request->user(), $id);
        return response()->json([
            'success' => true,
            'message' => 'Xóa mã giảm giá thành công.',
            'data' => (new InstructorCouponDeleteResource($result))->resolve($request),
        ]);
    }
    public function couponCourseOptions(InstructorCouponCourseOptionRequest $request): JsonResponse
    {
        $courses = $this->instructorCouponService->courseOptions(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách khóa học cho mã giảm giá thành công.',
            'data' => InstructorCouponCourseOptionResource::collection($courses)->resolve($request),
        ]);
    }
    public function banners(Request $request, mixed $id = null): JsonResponse
    {
        if ($id !== null) {
            $pathValidator = Validator::make(['id' => $id], [
                'id' => 'required|integer|min:1',
            ]);
            if ($pathValidator->fails()) {
                return ApiResponse::error('Dữ liệu không hợp lệ.', $pathValidator->errors()->toArray(), 422);
            }
            $id = (int) $id;
        }
        if ($request->isMethod('get') && $id !== null) {
            $banner = $this->marketingService->getBanner($id);
            return ApiResponse::success(
                new BannerResource($banner),
                'Thao tác thành công',
                200
            );
        }
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