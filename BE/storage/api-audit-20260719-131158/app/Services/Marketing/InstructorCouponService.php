<?php
namespace App\Services\Marketing;
use App\Exceptions\BusinessException;
use App\Models\Coupon;
use App\Repositories\Marketing\InstructorCouponRepository;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
final class InstructorCouponService
{
    public function __construct(
        private readonly InstructorCouponRepository $coupons,
        private readonly DatabaseManager $database
    ) {
    }
    /**
     * @throws AuthenticationException
     */
    public function getSummary(?object $authUser, array $filters): array
    {
        $instructorId = $this->instructorId($authUser);
        $this->validateOwnedCourseFilter($instructorId, $filters);
        return $this->coupons->summary($instructorId, $filters);
    }
    /**
     * @throws AuthenticationException
     */
    public function paginateCoupons(?object $authUser, array $filters): LengthAwarePaginator
    {
        $instructorId = $this->instructorId($authUser);
        $this->validateOwnedCourseFilter($instructorId, $filters);
        return $this->coupons->paginateCoupons($instructorId, $filters);
    }
    /**
     * @throws AuthenticationException
     */
    public function createCoupon(?object $authUser, array $data): Coupon
    {
        $instructorId = $this->instructorId($authUser);
        $courseId = (int) ($data['course_id'] ?? 0);
        $this->ensureCourseOwnedByInstructor($courseId, $instructorId);
        $this->assertDiscountRule(
            (string) ($data['discount_type'] ?? ''),
            $data['discount_value'] ?? null
        );
        $this->assertDateRange($data['start_at'] ?? null, $data['end_at'] ?? null);
        $status = $data['status'] ?? Coupon::STATUS_ACTIVE;
        if ($status === Coupon::STATUS_ACTIVE) {
            $this->assertCanActivateCoupon(
                $data['end_at'] ?? null,
                $data['usage_limit'] ?? null,
                0
            );
        }
        $payload = [
            'user_id' => $instructorId,
            'course_id' => $courseId,
            'code' => $this->normalizeCode((string) $data['code']),
            'name' => trim((string) $data['name']),
            'description' => $data['description'] ?? null,
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'max_order_amount' => $data['max_order_amount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'used_count' => 0,
            'start_at' => $data['start_at'] ?? null,
            'end_at' => $data['end_at'] ?? null,
            'status' => $status,
        ];
        return $this->database->transaction(function () use ($payload): Coupon {
            try {
                return $this->coupons->create($payload);
            } catch (QueryException $exception) {
                if ((string) $exception->getCode() === '23000') {
                    throw new BusinessException('Mã giảm giá đã tồn tại.', 409);
                }
                throw $exception;
            }
        });
    }
    /**
     * @throws AuthenticationException
     */
    public function showCoupon(?object $authUser, int $couponId): Coupon
    {
        $instructorId = $this->instructorId($authUser);
        return $this->getOwnedCoupon($couponId, $instructorId);
    }
    /**
     * @throws AuthenticationException
     */
    public function updateCoupon(?object $authUser, int $couponId, array $data): Coupon
    {
        $instructorId = $this->instructorId($authUser);
        $coupon = $this->getOwnedCoupon($couponId, $instructorId);
        $payload = [];
        if (array_key_exists('course_id', $data)) {
            $courseId = (int) $data['course_id'];
            $this->ensureCourseOwnedByInstructor($courseId, $instructorId);
            $payload['course_id'] = $courseId;
        }
        if (array_key_exists('code', $data)) {
            $nextCode = $this->normalizeCode((string) $data['code']);
            if ($nextCode !== (string) $coupon->code && (int) $coupon->used_count > 0) {
                throw new BusinessException('Không thể sửa mã coupon đã được sử dụng.', 409);
            }
            $payload['code'] = $nextCode;
        }
        if (array_key_exists('name', $data)) {
            $payload['name'] = trim((string) $data['name']);
        }
        if (array_key_exists('description', $data)) {
            $payload['description'] = $data['description'];
        }
        if (array_key_exists('discount_type', $data)) {
            $payload['discount_type'] = $data['discount_type'];
        }
        if (array_key_exists('discount_value', $data)) {
            $payload['discount_value'] = $data['discount_value'];
        }
        if (array_key_exists('max_order_amount', $data)) {
            $payload['max_order_amount'] = $data['max_order_amount'];
        }
        if (array_key_exists('usage_limit', $data)) {
            if ($data['usage_limit'] !== null && (int) $data['usage_limit'] < (int) $coupon->used_count) {
                throw new BusinessException('Giới hạn lượt dùng không được nhỏ hơn số lượt đã dùng.', 422, [
                    'usage_limit' => ['Giới hạn lượt dùng không được nhỏ hơn số lượt đã dùng.'],
                ]);
            }
            $payload['usage_limit'] = $data['usage_limit'];
        }
        if (array_key_exists('start_at', $data)) {
            $payload['start_at'] = $data['start_at'];
        }
        if (array_key_exists('end_at', $data)) {
            $payload['end_at'] = $data['end_at'];
        }
        if (array_key_exists('status', $data)) {
            $payload['status'] = $data['status'];
        }
        $nextDiscountType = (string) ($payload['discount_type'] ?? $coupon->discount_type);
        $nextDiscountValue = $payload['discount_value'] ?? $coupon->discount_value;
        $nextStartAt = array_key_exists('start_at', $payload) ? $payload['start_at'] : $coupon->start_at;
        $nextEndAt = array_key_exists('end_at', $payload) ? $payload['end_at'] : $coupon->end_at;
        $nextUsageLimit = array_key_exists('usage_limit', $payload) ? $payload['usage_limit'] : $coupon->usage_limit;
        $nextStatus = (string) ($payload['status'] ?? $coupon->status);
        $this->assertDiscountRule($nextDiscountType, $nextDiscountValue);
        $this->assertDateRange($nextStartAt, $nextEndAt);
        if ($nextStatus === Coupon::STATUS_ACTIVE) {
            $this->assertCanActivateCoupon($nextEndAt, $nextUsageLimit, (int) $coupon->used_count);
        }
        return $this->database->transaction(function () use ($coupon, $payload): Coupon {
            try {
                return $this->coupons->update($coupon, $payload);
            } catch (QueryException $exception) {
                if ((string) $exception->getCode() === '23000') {
                    throw new BusinessException('Mã giảm giá đã tồn tại.', 409);
                }
                throw $exception;
            }
        });
    }
    /**
     * @throws AuthenticationException
     */
    public function updateCouponStatus(?object $authUser, int $couponId, string $status): Coupon
    {
        $instructorId = $this->instructorId($authUser);
        $coupon = $this->getOwnedCoupon($couponId, $instructorId);
        if ($status === Coupon::STATUS_ACTIVE) {
            $this->assertCanActivateCoupon(
                $coupon->end_at,
                $coupon->usage_limit,
                (int) $coupon->used_count
            );
        }
        return $this->database->transaction(function () use ($coupon, $status): Coupon {
            return $this->coupons->update($coupon, [
                'status' => $status,
            ]);
        });
    }
    /**
     * @throws AuthenticationException
     */
    public function deleteCoupon(?object $authUser, int $couponId): array
    {
        $instructorId = $this->instructorId($authUser);
        $coupon = $this->getOwnedCoupon($couponId, $instructorId);
        $id = (int) $coupon->id;
        $this->database->transaction(function () use ($coupon): void {
            $this->coupons->delete($coupon);
        });
        return [
            'id' => $id,
        ];
    }
    /**
     * @throws AuthenticationException
     */
    public function courseOptions(?object $authUser, array $filters): Collection
    {
        $instructorId = $this->instructorId($authUser);
        return $this->coupons->courseOptions($instructorId, $filters);
    }
    /**
     * @throws AuthenticationException
     */
    private function instructorId(?object $authUser): int
    {
        if ($authUser === null || empty($authUser->id)) {
            throw new AuthenticationException('Unauthenticated.');
        }
        return (int) $authUser->id;
    }
    private function validateOwnedCourseFilter(int $instructorId, array $filters): void
    {
        if (empty($filters['course_id'])) {
            return;
        }
        $this->ensureCourseOwnedByInstructor((int) $filters['course_id'], $instructorId);
    }
    private function ensureCourseOwnedByInstructor(int $courseId, int $instructorId): void
    {
        if ($courseId <= 0 || $this->coupons->courseOwnedByInstructor($courseId, $instructorId) === null) {
            throw new BusinessException('Không tìm thấy khóa học hoặc bạn không có quyền thao tác.', 404);
        }
    }
    private function getOwnedCoupon(int $couponId, int $instructorId): Coupon
    {
        if ($couponId <= 0) {
            throw new BusinessException('Không tìm thấy mã giảm giá.', 404);
        }
        $coupon = $this->coupons->findOwnedCoupon($couponId, $instructorId);
        if ($coupon === null) {
            throw new BusinessException('Không tìm thấy mã giảm giá hoặc bạn không có quyền thao tác.', 404);
        }
        return $coupon;
    }
    private function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }
    private function assertDiscountRule(string $discountType, mixed $discountValue): void
    {
        $value = (float) $discountValue;
        if (!in_array($discountType, [Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED], true)) {
            throw new BusinessException('Loại giảm giá không hợp lệ.', 422, [
                'discount_type' => ['Loại giảm giá không hợp lệ.'],
            ]);
        }
        if ($value <= 0) {
            throw new BusinessException('Giá trị giảm phải lớn hơn 0.', 422, [
                'discount_value' => ['Giá trị giảm phải lớn hơn 0.'],
            ]);
        }
        if ($discountType === Coupon::TYPE_PERCENT && $value > 100) {
            throw new BusinessException('Giá trị phần trăm giảm không hợp lệ.', 422, [
                'discount_value' => ['Giá trị phần trăm giảm không được vượt quá 100.'],
            ]);
        }
    }
    private function assertDateRange(mixed $startAt, mixed $endAt): void
    {
        if ($startAt === null || $endAt === null) {
            return;
        }
        if (Carbon::parse($endAt)->lt(Carbon::parse($startAt))) {
            throw new BusinessException('Ngày kết thúc không được trước ngày bắt đầu.', 422, [
                'end_at' => ['Ngày kết thúc không được trước ngày bắt đầu.'],
            ]);
        }
    }
    private function assertCanActivateCoupon(mixed $endAt, mixed $usageLimit, int $usedCount): void
    {
        if ($endAt !== null && Carbon::parse($endAt)->lt(now())) {
            throw new BusinessException('Mã giảm giá đã hết hạn, không thể bật hoạt động.', 409);
        }
        if ($usageLimit !== null && $usedCount >= (int) $usageLimit) {
            throw new BusinessException('Mã giảm giá đã hết lượt dùng, không thể bật hoạt động.', 409);
        }
    }
}