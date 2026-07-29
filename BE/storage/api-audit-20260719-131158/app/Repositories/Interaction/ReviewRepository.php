<?php
namespace App\Repositories\Interaction;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Order;
class ReviewRepository
{
    public function findPublishedCourse(int $courseId): ?Course
    {
        return Course::query()
            ->whereKey($courseId)
            ->where('status', 'published')
            ->first();
    }
    public function findPaidOrderForUserCourse(int $userId, int $courseId): ?Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', Order::STATUS_PAID)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->first();
    }
    public function hasActiveEnrollment(int $userId, int $courseId): bool
    {
        return Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereIn('status', [
                Enrollment::STATUS_ACTIVE,
                Enrollment::STATUS_COMPLETED,
            ])
            ->exists();
    }
    public function hasReviewForUserCourse(int $userId, int $courseId): bool
    {
        return CourseReview::withTrashed()
            ->whereHas('order', function ($query) use ($userId, $courseId): void {
                $query->where('user_id', $userId)
                    ->where('course_id', $courseId)
                    ->where('status', Order::STATUS_PAID)
                    ->where('payment_status', Order::PAYMENT_PAID);
            })
            ->exists();
    }
    public function createReview(Order $order, int $rating, ?string $comment): CourseReview
    {
        return CourseReview::query()
            ->create([
                'order_id' => $order->id,
                'rating' => $rating,
                'comment' => $comment,
            ])
            ->load('order');
    }
}