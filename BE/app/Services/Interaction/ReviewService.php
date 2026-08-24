<?php
namespace App\Services\Interaction;
use App\Models\CourseReview;
use App\Models\User;
use App\Repositories\Interaction\ReviewRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
class ReviewService
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository
    ) {
    }
    public function storeReview(int $courseId, array $payload, User $learner): CourseReview
    {
        try {
            return DB::transaction(function () use ($courseId, $payload, $learner): CourseReview {
                if (! $learner->isActive()) {
                    throw new HttpException(403, 'Tài khoản của bạn không thể đánh giá khóa học.');
                }
                $course = $this->reviewRepository->findPublishedCourse($courseId);
                if ($course === null) {
                    throw new HttpException(404, 'Không tìm thấy khóa học.');
                }
                $paidOrder = $this->reviewRepository->findPaidOrderForUserCourse(
                    userId: (int) $learner->id,
                    courseId: $courseId
                );
                $hasActiveEnrollment = $this->reviewRepository->hasActiveEnrollment(
                    userId: (int) $learner->id,
                    courseId: $courseId
                );
                if ($paidOrder === null && ! $hasActiveEnrollment) {
                    throw new HttpException(403, 'Bạn cần đăng ký hoặc mua khóa học trước khi đánh giá.');
                }

                $reviewContent = $payload['content'] ?? $payload['comment'] ?? null;
                if ($reviewContent !== null && trim($reviewContent) !== '') {
                    $modResult = app(\App\Services\Moderation\ContentModeratorService::class)->inspect($reviewContent);
                    if ($modResult['is_violating']) {
                        throw new HttpException(422, 'Nội dung đánh giá không phù hợp: ' . $modResult['reason']);
                    }
                }

                return $this->reviewRepository->createReview(
                    order: $paidOrder,
                    rating: (int) $payload['rating'],
                    comment: $reviewContent
                );
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                throw new HttpException(409, 'Bạn đã đánh giá khóa học này.');
            }
            throw $exception;
        }
    }
}