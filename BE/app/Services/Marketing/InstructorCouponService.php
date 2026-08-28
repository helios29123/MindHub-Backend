<?php

namespace App\Services\Marketing;

use App\Models\Coupon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class InstructorCouponService
{
    public function __construct(
        private readonly CouponService $coupons
    ) {
    }

    public function getSummary(?object $authUser, array $filters): array
    {
        return $this->coupons->summaryForInstructor($this->instructorId($authUser), $filters);
    }

    public function paginateCoupons(?object $authUser, array $filters): LengthAwarePaginator
    {
        return $this->coupons->paginateForInstructor($this->instructorId($authUser), $filters);
    }

    public function createCoupon(?object $authUser, array $data): Coupon
    {
        return $this->coupons->createForInstructor($this->instructorId($authUser), $data);
    }

    public function showCoupon(?object $authUser, int $couponId): Coupon
    {
        return $this->coupons->getForInstructor($this->instructorId($authUser), $couponId);
    }

    public function updateCoupon(?object $authUser, int $couponId, array $data): Coupon
    {
        return $this->coupons->updateForInstructor($this->instructorId($authUser), $couponId, $data);
    }

    public function updateCouponStatus(?object $authUser, int $couponId, string $status): Coupon
    {
        return $this->coupons->updateForInstructor($this->instructorId($authUser), $couponId, [
            'status' => $status,
        ]);
    }

    public function deleteCoupon(?object $authUser, int $couponId): array
    {
        $coupon = $this->coupons->deleteForInstructor($this->instructorId($authUser), $couponId);

        return ['id' => (int) $coupon->id];
    }

    public function courseOptions(?object $authUser, array $filters): Collection
    {
        return $this->coupons->courseOptionsForInstructor($this->instructorId($authUser), $filters);
    }

    private function instructorId(?object $authUser): int
    {
        if ($authUser === null || empty($authUser->id)) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return (int) $authUser->id;
    }
}
