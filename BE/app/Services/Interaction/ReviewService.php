<?php
namespace App\Services\Interaction;
use App\Models\CourseReview; use App\Models\User; use App\Repositories\Interaction\ReviewRepository; use Illuminate\Database\QueryException; use Illuminate\Support\Facades\DB; use Symfony\Component\HttpKernel\Exception\HttpException;
class ReviewService {
    public function __construct(private readonly ReviewRepository $reviewRepository) {}
    public function storeReview(int $courseId,array $payload,User $learner): CourseReview {
        try { return DB::transaction(function() use($courseId,$payload,$learner){
            if(!$learner->isActive()) throw new HttpException(403,'Tài khoản của bạn không thể đánh giá.');
            if(!$this->reviewRepository->findPublishedCourse($courseId)) throw new HttpException(404,'Không tìm thấy khóa học.');
            $o=$this->reviewRepository->findRealPaidOrderForUserCourse((int)$learner->id,$courseId); $e=$this->reviewRepository->findValidEnrollment((int)$learner->id,$courseId);
            if(!$o || !$e) throw new HttpException(403,'Học thử không được đánh giá; cần mua khóa học thật.');
            if(!$this->reviewRepository->hasCompletedLesson((int)$e->id)) throw new HttpException(403,'Bạn cần hoàn thành ít nhất một bài học trước khi đánh giá.');
            if($this->reviewRepository->hasReviewForUserCourse((int)$learner->id,$courseId)) throw new HttpException(409,'Bạn đã đánh giá khóa học này.');
            return $this->reviewRepository->createReview($o,(int)$payload['rating'],$payload['content']??null);
        }); } catch(QueryException $x){ if($x->getCode()==='23000') throw new HttpException(409,'Bạn đã đánh giá khóa học này.'); throw $x; }
    }
    public function updateReview(int $reviewId,array $payload,User $learner): CourseReview { if(!$learner->isActive()) throw new HttpException(403,'Tài khoản không thể sửa đánh giá.'); $r=$this->reviewRepository->findOwnedReview($reviewId,(int)$learner->id); if(!$r) throw new HttpException(404,'Không tìm thấy đánh giá.'); return $this->reviewRepository->updateReview($r,(int)$payload['rating'],$payload['content']??null); }
}
