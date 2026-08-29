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
        if (! $order->isPaid()) {
            throw new BusinessException('Order chưa đủ điều kiện ghi danh.', 400);
        }

        $sameOrderEnrollment = $this->enrollmentRepository->findByOrderId($order->id);
        if ($sameOrderEnrollment) {
            return $sameOrderEnrollment;
        }

        $existing = $this->enrollmentRepository->findByUserAndCourse(
            (int) $order->user_id,
            (int) $order->course_id
        );

        if ($existing) {
            // Upgrade trial -> paid: giữ nguyên enrollment.id + progress/completed state,
            // đổi order sở hữu hiện tại sang paid order và bỏ expiry.
            $existing->order_id = $order->id;
            $existing->expires_at = null;

            if ($existing->status !== Enrollment::STATUS_COMPLETED) {
                $existing->status = Enrollment::STATUS_ACTIVE;
            }

            $existing->save();

            return $existing->refresh();
        }

        $enrollment = $this->enrollmentRepository->create([
            'user_id' => $order->user_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'progress_percent' => 0,
            'enrolled_at' => now(),
            'expires_at' => null,
        ]);

        try {
            $user = \App\Models\User::find($order->user_id);
            $course = \App\Models\Course::with('instructor')->find($order->course_id);

            if ($user && $course && ! empty($user->email)) {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\CourseWelcomeMail($user, $course, $order));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(
                'Failed to send CourseWelcomeMail in EnrollmentAfterPaymentService: ' . $e->getMessage()
            );
        }

        return $enrollment;
    }
}
