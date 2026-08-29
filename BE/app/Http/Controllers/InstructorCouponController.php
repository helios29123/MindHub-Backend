<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marketing\InstructorCouponIndexRequest;
use App\Http\Requests\Marketing\InstructorCouponStatusRequest;
use App\Http\Requests\Marketing\InstructorCouponStoreRequest;
use App\Http\Requests\Marketing\InstructorCouponUpdateRequest;
use App\Http\Resources\Marketing\InstructorCouponCourseOptionResource;
use App\Http\Resources\Marketing\InstructorCouponDeleteResource;
use App\Http\Resources\Marketing\InstructorCouponDetailResource;
use App\Http\Resources\Marketing\InstructorCouponResource;
use App\Http\Resources\Marketing\InstructorCouponSummaryResource;
use App\Models\Coupon;
use App\Services\Marketing\CouponService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InstructorCouponController extends Controller
{
    public function __construct(
        private readonly CouponService $couponService
    ) {
    }

    public function summary(Request $request): JsonResponse
    {
        $summary = $this->couponService->summaryForInstructor(
            (int) $request->user()->id,
            $request->all()
        );

        return ApiResponse::success(
            new InstructorCouponSummaryResource((object) $summary),
            'Lấy thông tin tổng quan campaign thành công.'
        );
    }

    public function index(InstructorCouponIndexRequest $request): JsonResponse
    {
        $paginator = $this->couponService->paginateForInstructor(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            InstructorCouponResource::collection(collect($paginator->items()))->resolve($request),
            'Lấy danh sách campaign hiện tại thành công.',
            200,
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            new InstructorCouponDetailResource(
                $this->couponService->getForInstructor((int) $request->user()->id, $id)
            ),
            'Lấy chi tiết campaign thành công.'
        );
    }

    public function store(InstructorCouponStoreRequest $request): JsonResponse
    {
        $coupon = $this->couponService->createForInstructor(
            (int) $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Tạo campaign thành công.',
            201
        );
    }

    public function update(InstructorCouponUpdateRequest $request, int $id): JsonResponse
    {
        $coupon = $this->couponService->updateForInstructor(
            (int) $request->user()->id,
            $id,
            $request->validated()
        );

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Cập nhật campaign thành công.'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $coupon = $this->couponService->deleteForInstructor(
            (int) $request->user()->id,
            $id
        );

        return ApiResponse::success(
            new InstructorCouponDeleteResource($coupon),
            'Đã tắt campaign.'
        );
    }

    public function updateStatus(InstructorCouponStatusRequest $request, int $id): JsonResponse
    {
        $coupon = $this->couponService->updateForInstructor(
            (int) $request->user()->id,
            $id,
            ['status' => $request->validated()['status']]
        );

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Đã tắt campaign.'
        );
    }

    public function enable(Request $request, int $id): JsonResponse
    {
        return ApiResponse::error(
            'Campaign đã kết thúc/đã tắt không được mở lại. Hãy tạo campaign mới.',
            [],
            409
        );
    }

    public function disable(Request $request, int $id): JsonResponse
    {
        $coupon = $this->couponService->updateForInstructor(
            (int) $request->user()->id,
            $id,
            ['status' => Coupon::STATUS_INACTIVE]
        );

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Đã tắt campaign.'
        );
    }

    public function courseOptions(Request $request): JsonResponse
    {
        $courses = $this->couponService->courseOptionsForInstructor(
            (int) $request->user()->id,
            $request->all()
        );

        return ApiResponse::success(
            InstructorCouponCourseOptionResource::collection($courses)->resolve($request),
            'Lấy danh sách khóa học thành công.'
        );
    }

    public function checkCode(Request $request): JsonResponse
    {
        return ApiResponse::error(
            'Mã campaign do Backend tự sinh, Instructor không cần kiểm tra hoặc nhập code.',
            [],
            410
        );
    }
}
