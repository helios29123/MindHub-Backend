<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marketing\InstructorCouponIndexRequest;
use App\Http\Requests\Marketing\InstructorCouponStoreRequest;
use App\Http\Requests\Marketing\InstructorCouponUpdateRequest;
use App\Http\Requests\Marketing\InstructorCouponStatusRequest;
use App\Http\Resources\Marketing\InstructorCouponResource;
use App\Http\Resources\Marketing\InstructorCouponDetailResource;
use App\Http\Resources\Marketing\InstructorCouponSummaryResource;
use App\Http\Resources\Marketing\InstructorCouponCourseOptionResource;
use App\Http\Resources\Marketing\InstructorCouponDeleteResource;
use App\Services\Marketing\CouponService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Coupon;

final class InstructorCouponController extends Controller
{
    public function __construct(
        private readonly CouponService $couponService
    ) {
    }

    public function summary(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $courseId = $request->query('course_id');
        if ($courseId) {
            $course = DB::table('courses')
                ->where('id', (int) $courseId)
                ->where('instructor_id', $instructorId)
                ->first();
            if (!$course) {
                return ApiResponse::error('Không tìm thấy khóa học.', [], 404);
            }
        }

        $summary = $this->getSummaryData($instructorId, $request->all());

        return ApiResponse::success(
            new InstructorCouponSummaryResource((object) $summary),
            'Lấy thông tin tổng quan coupon thành công.'
        );
    }

    public function index(InstructorCouponIndexRequest $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $paginator = $this->couponService->paginateForInstructor($instructorId, $request->validated());

        return ApiResponse::success(
            InstructorCouponResource::collection(collect($paginator->items()))->resolve($request),
            'Lấy danh sách coupon thành công.',
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
        $instructorId = (int) $request->user()->id;
        $coupon = $this->couponService->getForInstructor($instructorId, $id);

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Lấy chi tiết coupon thành công.'
        );
    }

    public function store(InstructorCouponStoreRequest $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $coupon = $this->couponService->createForInstructor($instructorId, $request->validated());

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Tạo coupon thành công.',
            201
        );
    }

    public function update(InstructorCouponUpdateRequest $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $coupon = $this->couponService->updateForInstructor($instructorId, $id, $request->validated());

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Cập nhật thông tin coupon thành công.'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $coupon = $this->couponService->deleteForInstructor($instructorId, $id);

        return ApiResponse::success(
            new InstructorCouponDeleteResource($coupon),
            'Xóa coupon thành công.'
        );
    }

    public function updateStatus(InstructorCouponStatusRequest $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $coupon = $this->couponService->updateForInstructor($instructorId, $id, [
            'status' => $request->validated()['status']
        ]);

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Cập nhật trạng thái coupon thành công.'
        );
    }

    public function enable(Request $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $coupon = $this->couponService->updateForInstructor($instructorId, $id, [
            'status' => 'active'
        ]);

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Kích hoạt coupon thành công.'
        );
    }

    public function disable(Request $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $coupon = $this->couponService->updateForInstructor($instructorId, $id, [
            'status' => 'inactive'
        ]);

        return ApiResponse::success(
            new InstructorCouponDetailResource($coupon),
            'Vô hiệu hóa coupon thành công.'
        );
    }

    public function courseOptions(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $query = DB::table('courses')
            ->where('instructor_id', $instructorId)
            ;

        if ($request->query('search')) {
            $query->where('title', 'like', '%' . $request->query('search') . '%');
        }

        $courses = $query->select('id', 'title', 'status')->get();

        return ApiResponse::success(
            InstructorCouponCourseOptionResource::collection($courses)->resolve($request),
            'Lấy danh sách khóa học thành công.'
        );
    }

    public function checkCode(Request $request): JsonResponse
    {
        $code = $request->query('code');
        if (!$code) {
            return ApiResponse::error('Thiếu mã coupon.', [], 422);
        }

        $exists = Coupon::where('code', $code)->exists();
        return ApiResponse::success(['exists' => $exists], 'Kiểm tra mã coupon thành công.');
    }

    private function getSummaryData(int $instructorId, array $filters): array
    {
        $query = Coupon::query()
            ->where('user_id', $instructorId)
            ->whereHas('course', function ($courseQuery) use ($instructorId): void {
                $courseQuery->where('instructor_id', $instructorId);
            });

        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }

        $coupons = $query->get();

        $total = 0;
        $active = 0;
        $inactive = 0;
        $expired = 0;
        $usedUp = 0;

        $now = now();

        foreach ($coupons as $coupon) {
            $total++;
            if ($coupon->status === 'inactive') {
                $inactive++;
            } elseif ($coupon->end_at && \Carbon\Carbon::parse($coupon->end_at)->isPast()) {
                $expired++;
            } elseif ($coupon->usage_limit !== null && (int)$coupon->used_count >= (int)$coupon->usage_limit) {
                $usedUp++;
            } else {
                $active++;
            }
        }

        $totalUsage = DB::table('coupons')
            ->where('user_id', $instructorId)
            
            ->sum('used_count');

        return [
            'total_coupons' => $total,
            'active_coupons' => $active,
            'inactive_coupons' => $inactive,
            'expired_coupons' => $expired,
            'used_up_coupons' => $usedUp,
            'total_usage_count' => (int) $totalUsage,
        ];
    }
}
