<?php

namespace App\Services\Interaction;

use App\Exceptions\BusinessException;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InteractionService
{
    public function getLessonComments(int $lessonId, array $queryParams, User $user): LengthAwarePaginator
    {
        // 1. Tìm lesson và kiểm tra status
        $lesson = Lesson::with('course')->find($lessonId);

        if (!$lesson) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $course = $lesson->course;
        if (!$course || $course->status !== 'published') {
            throw new BusinessException('Nội dung chưa khả dụng.', 403);
        }

        if ($lesson->status !== 'published') {
            throw new BusinessException('Nội dung chưa khả dụng.', 403);
        }

        // 2. Kiểm tra learner có enrollment active/completed hoặc là giảng viên/admin
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $lesson->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->where(function ($query) { $query->whereNull('expires_at')->orWhere('expires_at', '>', now()); })
            ->first();

        if (!$enrollment && (int)$course->instructor_id !== (int)$user->id && $user->role !== 'admin' && (float)$course->price != 0) {
            throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        // 3. Query và phân trang comments
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        
        return Comment::where('lesson_id', $lesson->id)
            ->where('status', 'visible')
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    public function createComment(int $lessonId, array $data, User $user): Comment
    {
        if(!$user->isActive()) throw new BusinessException('Tài khoản hiện không thể dùng Q&A.',403);
        $lesson=Lesson::with(['course','section'])->find($lessonId); if(!$lesson || !$lesson->course || $lesson->course->status!=='published' || $lesson->status!=='published') throw new BusinessException('Nội dung chưa khả dụng.',403);
        $e=Enrollment::query()->where('user_id',$user->id)->where('course_id',$lesson->course_id)->whereIn('status',[Enrollment::STATUS_ACTIVE,Enrollment::STATUS_COMPLETED])->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->first();
        if(!$e) {
            if ((int)$lesson->course->instructor_id === (int)$user->id || $user->role === 'admin' || (float)$lesson->course->price == 0) {
                $e = Enrollment::firstOrCreate(
                    ['user_id' => $user->id, 'course_id' => $lesson->course_id],
                    ['status' => Enrollment::STATUS_ACTIVE, 'enrolled_at' => now()]
                );
            } else {
                throw new BusinessException('Bạn không còn quyền tạo Q&A.',403);
            }
        }
        $parent=$data['parent_id']??null; if($parent!==null && !Comment::query()->whereKey($parent)->where('lesson_id',$lessonId)->whereNull('parent_id')->where('status','visible')->exists()) throw new BusinessException('Chỉ được reply câu hỏi gốc đang hiển thị.',422);
        return Comment::create(['parent_id'=>$parent,'enrollment_id'=>$e->id,'user_id'=>$user->id,'lesson_id'=>$lessonId,'content'=>$data['content'],'status'=>'visible','is_official'=>false])->load('user');
    }
    public function replyToComment(int $commentId, array $data, User $user): Comment
    {
        if(!$user->isActive()) throw new BusinessException('Tài khoản hiện không thể dùng Q&A.',403);
        $p=Comment::query()->whereKey($commentId)->whereNull('parent_id')->where('status','visible')->with('lesson.course')->first();
        if(!$p || !$p->lesson || !$p->lesson->course) throw new BusinessException('Không tìm thấy dữ liệu.',404);
        if((int)$p->lesson->course->instructor_id!==(int)$user->id) throw new BusinessException('Bạn không được trả lời Q&A này.',403);
        return Comment::create(['parent_id'=>$p->id,'enrollment_id'=>null,'user_id'=>$user->id,'lesson_id'=>$p->lesson_id,'content'=>$data['content'],'status'=>'visible','is_official'=>true])->load('user');
    }
}
