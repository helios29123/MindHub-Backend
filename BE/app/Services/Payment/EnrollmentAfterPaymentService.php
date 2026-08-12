<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Models\Enrollment;
use App\Models\Order;
use App\Repositories\Payment\EnrollmentRepository;

class EnrollmentAfterPaymentService
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository
    ) {
    }

    public function createEnrollmentAfterPayment(Order $order): Enrollment
    {
        if (!$order->isPaid()) {
            throw new BusinessException('Order chưa đủ điều kiện ghi danh.', 400);
        }

        $existingEnrollment = $this->enrollmentRepository->findByOrderId($order->id);

        if ($existingEnrollment) {
            return $existingEnrollment;
        }

        $existingCourseEnrollment = $this->enrollmentRepository->findByUserAndCourse(
            $order->user_id,
            $order->course_id
        );

        if ($existingCourseEnrollment) {
            return $existingCourseEnrollment;
        }

        $enrollment = $this->enrollmentRepository->create([
            'user_id' => $order->user_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'progress_percent' => 0,
            'enrolled_at' => now(),
        ]);

        try {
            $user = \App\Models\User::find($order->user_id);
            $course = \App\Models\Course::with('instructor')->find($order->course_id);

            if ($user && $course && ! empty($user->email)) {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\CourseWelcomeMail($user, $course, $order));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send CourseWelcomeMail in EnrollmentAfterPaymentService: ' . $e->getMessage());
        }

        return $enrollment;
    }
}
