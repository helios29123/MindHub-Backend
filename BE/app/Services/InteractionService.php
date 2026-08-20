<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\Order;
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

        // 2. Kiểm tra learner có enrollment active/completed
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $lesson->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        // 3. Query và phân trang comments
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        
        return Comment::where('lesson_id', $lesson->id)
            ->where('status', 'visible')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function createComment(int $lessonId, array $data, User $user): Comment
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

        // 2. Kiểm tra learner có enrollment active/completed
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $lesson->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        // 3. Kiểm tra parent_id nếu có
        $parentId = $data['parent_id'] ?? null;
        if ($parentId !== null) {
            $parentComment = Comment::where('id', $parentId)
                ->where('lesson_id', $lessonId)
                ->where('status', 'visible')
                ->first();

            if (!$parentComment) {
                throw new BusinessException('Dữ liệu không hợp lệ.', 422, [
                    'parent_id' => ['Bình luận trả lời không hợp lệ hoặc đã bị ẩn.']
                ]);
            }
        }

        // 4. Tìm kiếm order paid liên quan đến khóa học
        $order = Order::where('user_id', $user->id)
            ->where('course_id', $lesson->course_id)
            ->where('status', 'paid')
            ->where('payment_status', 'paid')
            ->first();

        // 5. Thêm comment mới
        $comment = Comment::create([
            'parent_id' => $parentId,
            'user_id' => $user->id,
            'order_id' => $order ? $order->id : null,
            'lesson_id' => $lessonId,
            'content' => $data['content'],
            'status' => 'visible',
        ]);

        return $comment->load('user');
    }

    public function replyToComment(int $commentId, array $data, User $user): Comment
    {
        // 1. Tìm comment gốc visible và lesson/course liên quan.
        $parentComment = Comment::where('id', $commentId)
            ->where('status', 'visible')
            ->first();

        if (!$parentComment) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $lesson = Lesson::with('course')->find($parentComment->lesson_id);
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

        // 2. Kiểm tra instructor hiện tại là có phải là giảng viên của khóa học không
        if ((int) $course->instructor_id !== (int) $user->id) {
            throw new BusinessException('Bạn không được trả lời Q&A của khóa học này.', 403);
        }

        // 3. Tạo bình luận phản hồi
        $reply = Comment::create([
            'parent_id' => $parentComment->id,
            'user_id' => $user->id,
            'order_id' => null,
            'lesson_id' => $parentComment->lesson_id,
            'content' => $data['content'],
            'status' => 'visible',
        ]);

        return $reply->load('user');
    }
}
