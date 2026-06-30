<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class CoursePurchaseGuardService
{
    public function assertCanBuyCourse(int $userId, int $courseId): stdClass
    {
        $courseQuery = DB::table('courses')
            ->where('id', $courseId);

        if (Schema::hasColumn('courses', 'deleted_at')) {
            $courseQuery->whereNull('deleted_at');
        }

        /** @var stdClass|null $course */
        $course = $courseQuery->first();

        if (! $course) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }

        if (($course->status ?? null) !== 'published') {
            throw new BusinessException('Khóa học không thể thêm vào đơn hàng.', 422);
        }

        if ((int) $course->instructor_id === $userId) {
            throw new BusinessException('Giảng viên không thể mua khóa học của chính mình.', 403);
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
            ->where(function ($query): void {
                $query->where('status', 'paid')
                    ->orWhere('payment_status', 'paid');
            });

        if (Schema::hasColumn('orders', 'deleted_at')) {
            $paidOrderQuery->whereNull('deleted_at');
        }

        if ($paidOrderQuery->exists()) {
            throw new BusinessException('Bạn đã thanh toán khóa học này.', 409);
        }

        return $course;
    }
}
