<?php
namespace App\Repositories\Interaction;
use App\Models\Course; use App\Models\CourseReview; use App\Models\Enrollment; use App\Models\Order; use Illuminate\Support\Facades\DB;
class ReviewRepository {
    public function findPublishedCourse(int $id): ?Course { return Course::query()->whereKey($id)->where('status','published')->first(); }
    public function findRealPaidOrderForUserCourse(int $userId,int $courseId): ?Order { return Order::query()->where('user_id',$userId)->where('course_id',$courseId)->where('status',Order::STATUS_PAID)->where('payment_status',Order::PAYMENT_PAID)->where('amount','>',0)->where(fn($q)=>$q->whereNull('payment_method')->orWhere('payment_method','<>','coupon_trial'))->orderByDesc('paid_at')->orderByDesc('id')->first(); }
    public function findValidEnrollment(int $userId,int $courseId): ?Enrollment { return Enrollment::query()->where('user_id',$userId)->where('course_id',$courseId)->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first(); }
    public function hasCompletedLesson(int $enrollmentId): bool { return DB::table('lesson_progress')->where('enrollment_id',$enrollmentId)->where('status','completed')->exists(); }
    public function hasReviewForUserCourse(int $userId,int $courseId): bool { return CourseReview::query()->whereHas('order',fn($q)=>$q->where('user_id',$userId)->where('course_id',$courseId))->exists(); }
    public function createReview(Order $o,int $r,?string $c): CourseReview { return CourseReview::create(['order_id'=>$o->id,'rating'=>$r,'comment'=>$c])->load('order'); }
    public function findOwnedReview(int $id,int $userId): ?CourseReview { return CourseReview::query()->whereKey($id)->whereHas('order',fn($q)=>$q->where('user_id',$userId))->first(); }
    public function updateReview(CourseReview $r,int $rating,?string $comment): CourseReview { $r->update(['rating'=>$rating,'comment'=>$comment,'edited_at'=>now()]); return $r->fresh('order'); }
}
