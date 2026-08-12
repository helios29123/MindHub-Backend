<?php
namespace App\Policies;
use App\Models\Coupon;
use App\Models\User;
class CouponPolicy
{
    public function manage(User $user, Coupon $coupon): bool
    {
        $course = $coupon->course;
        return $user->isInstructor()
            && $course !== null
            && (int) $coupon->user_id === (int) $user->id
            && (int) $course->instructor_id === (int) $user->id;
    }
}