<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CoursePurchaseGuardService
{
    public function assertCanBuyCourse(int $userId, int $courseId): object
    {
        $courseQuery = DB::table('courses')
            ->where('id', $courseId);

        if (Schema::hasColumn('courses', 'deleted_at')) {
            $courseQuery->whereNull('deleted_at');
        }

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

        if (Schema::hasColumn('enrollments', 'deleted_at')) {
            $enrollmentQuery->whereNull('deleted_at');
        }

        if ($enrollmentQuery->exists()) {
            throw new BusinessException('Bạn đã sở hữu khóa học này.', 409);
        }

        $paidOrderQuery = DB::table('orders')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('payment_status', 'paid');

        if (Schema::hasColumn('orders', 'deleted_at')) {
            $paidOrderQuery->whereNull('deleted_at');
        }

        if ($paidOrderQuery->exists()) {
            throw new BusinessException('Bạn đã thanh toán khóa học này.', 409);
        }

        return $course;
    }
}
