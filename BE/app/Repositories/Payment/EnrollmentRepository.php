<?php

namespace App\Repositories\Payment;

use App\Models\Enrollment;

class EnrollmentRepository
{
    public function findByOrderId(int $orderId): ?Enrollment
    {
        return Enrollment::where('order_id', $orderId)->first();
    }
    public function findByUserAndCourse(int $userId, int $courseId): ?Enrollment
    {
        return Enrollment::where('user_id', $userId)->where('course_id', $courseId)->first();
    }
    public function create(array $enrollmentData): Enrollment
    {
        return Enrollment::create($enrollmentData);
    }
}
