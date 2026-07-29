<?php
namespace App\Services\Marketing;
use App\Exceptions\BusinessException;
use App\Models\Coupon;
use App\Models\Course;
use App\Repositories\Marketing\MarketingCouponRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
class CouponService
{
    public function __construct(
        private readonly MarketingCouponRepository $couponRepository
    ) {
    }
    public function paginateForInstructor(int $instructorId, array $filters): LengthAwarePaginator
    {
        if (!empty($filters['course_id'])) {
            $this->ensureCourseOwnedByInstructor((int) $filters['course_id'], $instructorId);
        }
        return $this->couponRepository->paginateForInstructor($instructorId, $filters);
    }
    public function getForInstructor(int $instructorId, int $couponId): Coupon
    {
        return $this->getCouponOwnedByInstructor($couponId, $instructorId);
    }
    public function createForInstructor(int $instructorId, array $data): Coupon
    {
        $this->ensureCourseOwnedByInstructor((int) $data['course_id'], $instructorId);
        $this->assertDiscountRule((string) $data['discount_type'], $data['discount_value']);
        $this->assertDateRange($data['start_at'] ?? null, $data['end_at'] ?? null);
        $payload = $data;
        $payload['user_id'] = $instructorId;
        $payload['status'] = $payload['status'] ?? Coupon::STATUS_ACTIVE;
        $payload['used_count'] = 0;
        return DB::transaction(function () use ($payload): Coupon {
            try {
                return $this->couponRepository->create($payload);
            } catch (QueryException $exception) {
                if ((string) $exception->getCode() === '23000') {
                    throw new BusinessException('Mã coupon đã tồn tại.', 409);
                }
                throw $exception;
            }
        });
    }
    public function updateForInstructor(int $instructorId, int $couponId, array $data): Coupon
    {
        $coupon = $this->getCouponOwnedByInstructor($couponId, $instructorId);
        if (array_key_exists('course_id', $data)) {
            $this->ensureCourseOwnedByInstructor((int) $data['course_id'], $instructorId);
        }
        $nextDiscountType = (string) ($data['discount_type'] ?? $coupon->discount_type);
        $nextDiscountValue = $data['discount_value'] ?? $coupon->discount_value;
        $nextStartAt = array_key_exists('start_at', $data) ? $data['start_at'] : $coupon->start_at;
        $nextEndAt = array_key_exists('end_at', $data) ? $data['end_at'] : $coupon->end_at;
        $this->assertDiscountRule($nextDiscountType, $nextDiscountValue);
        $this->assertDateRange($nextStartAt, $nextEndAt);
        if (
            array_key_exists('usage_limit', $data)
            && $data['usage_limit'] !== null
            && (int) $data['usage_limit'] < (int) $coupon->used_count
        ) {
            throw new BusinessException('Giới hạn lượt dùng không được nhỏ hơn số lượt đã dùng.', 422, [
                'usage_limit' => ['Giới hạn lượt dùng không được nhỏ hơn số lượt đã dùng.'],
            ]);
        }
        return DB::transaction(function () use ($coupon, $data): Coupon {
            try {
                return $this->couponRepository->update($coupon, $data);
            } catch (QueryException $exception) {
                if ((string) $exception->getCode() === '23000') {
                    throw new BusinessException('Mã coupon đã tồn tại.', 409);
                }
                throw $exception;
            }
        });
    }
    public function deleteForInstructor(int $instructorId, int $couponId): Coupon
    {
        $coupon = $this->getCouponOwnedByInstructor($couponId, $instructorId);
        return DB::transaction(function () use ($coupon): Coupon {
            return $this->couponRepository->delete($coupon);
        });
    }
    private function ensureCourseOwnedByInstructor(int $courseId, int $instructorId): Course
    {
        $course = $this->couponRepository->findCourseById($courseId);
        if (!$course) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }
        if ((int) $course->instructor_id !== $instructorId) {
            throw new BusinessException('Bạn không có quyền quản lý coupon cho khóa học này.', 403);
        }
        return $course;
    }
    private function getCouponOwnedByInstructor(int $couponId, int $instructorId): Coupon
    {
        $coupon = $this->couponRepository->findById($couponId);
        if (!$coupon) {
            throw new BusinessException('Không tìm thấy coupon hợp lệ.', 404);
        }
        if (
            (int) $coupon->user_id !== $instructorId
            || $coupon->course_id === null
            || !$coupon->course
            || (int) $coupon->course->instructor_id !== $instructorId
        ) {
            throw new BusinessException('Bạn không có quyền quản lý coupon cho khóa học này.', 403);
        }
        return $coupon;
    }
    private function assertDiscountRule(string $discountType, mixed $discountValue): void
    {
        if ($discountType === Coupon::TYPE_PERCENT && (float) $discountValue > 100) {
            throw new BusinessException('Thông tin coupon không hợp lệ.', 422, [
                'discount_value' => ['Giảm giá phần trăm không được vượt quá 100.'],
            ]);
        }
    }
    private function assertDateRange(mixed $startAt, mixed $endAt): void
    {
        if ($startAt === null || $endAt === null) {
            return;
        }
        if (Carbon::parse($endAt)->lte(Carbon::parse($startAt))) {
            throw new BusinessException('Thông tin coupon không hợp lệ.', 422, [
                'end_at' => ['Thời gian kết thúc phải sau thời gian bắt đầu.'],
            ]);
        }
    }
}