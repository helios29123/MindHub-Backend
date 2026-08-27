<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CoursePurchaseGuardService
{
    public function assertCanBuyCourse(int $userId, int $courseId): object
    {
        $courseQuery = DB::table('courses')
            ->where('id', $courseId);
$course = $courseQuery->first();

        if (! $course) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }

        if ((string) ($course->status ?? '') !== 'published') {
            throw new BusinessException('Khóa học chưa được xuất bản.', 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Instructor mua khóa học của instructor khác
        |--------------------------------------------------------------------------
        | User không được mua khóa học do chính mình tạo.
        | Rule này áp dụng cho cả instructor để tránh tự mua khóa của mình.
        */
        if ((int) ($course->instructor_id ?? 0) === $userId) {
            throw new BusinessException('Bạn không thể mua khóa học của chính mình.', 409);
        }

        $enrollmentQuery = DB::table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId);
if ($enrollmentQuery->exists()) {
            throw new BusinessException('Bạn đã sở hữu khóa học này.', 409);
        }

        $paidOrderQuery = DB::table('orders')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', Order::STATUS_PAID)
            ->where('payment_status', Order::PAYMENT_PAID);
if ($paidOrderQuery->exists()) {
            throw new BusinessException('Bạn đã thanh toán khóa học này.', 409);
        }

        return $course;
    }
}
