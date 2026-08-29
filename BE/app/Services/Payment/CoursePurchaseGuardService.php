<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;

class CoursePurchaseGuardService
{
    public function assertCanBuyCourse(int $userId, int $courseId): object
    {
        $course = DB::table('courses')
            ->where('id', $courseId)
            ->first();

        if (! $course) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }

        if ((string) $course->status !== 'published') {
            throw new BusinessException('Khóa học chưa được xuất bản.', 403);
        }

        if ((int) $course->instructor_id === $userId) {
            throw new BusinessException('Bạn không thể mua khóa học của chính mình.', 409);
        }

        $enrollment = DB::table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        // Enrollment chính thức: không được mua lại.
        // Enrollment có expires_at là trial: được phép mua thật để upgrade và giữ progress.
        if ($enrollment && $enrollment->expires_at === null) {
            throw new BusinessException('Bạn đã sở hữu khóa học này.', 409);
        }

        $officialPaidOrderExists = DB::table('orders')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('payment_status', 'paid')
            ->where(function ($query): void {
                $query->where('amount', '>', 0)
                    ->orWhereNull('payment_method')
                    ->orWhere('payment_method', '!=', 'coupon_trial');
            })
            ->exists();

        if ($officialPaidOrderExists) {
            throw new BusinessException('Bạn đã thanh toán khóa học này.', 409);
        }

        return $course;
    }
}
