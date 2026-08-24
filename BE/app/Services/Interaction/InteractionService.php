<?php

namespace App\Services\Interaction;

use App\Exceptions\BusinessException;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Services\Moderation\ContentModeratorService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InteractionService
{
    public function __construct(
        private readonly ContentModeratorService $moderatorService = new ContentModeratorService()
    ) {
    }

    public function getLessonComments(int $lessonId, array $queryParams, User $user): LengthAwarePaginator
    {
        // 1. Tìm lesson và kiểm tra status
        $lesson = Lesson::with('course')->find($lessonId);

        if (!$lesson) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $course = $lesson->course;
        if (!$course) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $isOwnerOrAdmin = ((int) $course->instructor_id === (int) $user->id) || in_array($user->role, ['admin', 'system_admin']);

        if (!$isOwnerOrAdmin) {
            if ($course->status !== 'published' || $lesson->status !== 'published') {
                throw new BusinessException('Nội dung chưa khả dụng.', 403);
            }

            if (!$lesson->is_preview) {
                $enrollment = Enrollment::where('user_id', $user->id)
                    ->where('course_id', $lesson->course_id)
                    ->whereIn('status', ['active', 'completed'])
                    ->first();

                if (!$enrollment) {
                    $hasPaidOrder = Order::where('user_id', $user->id)
                        ->where('course_id', $lesson->course_id)
                        ->whereIn('status', ['paid', 'completed'])
                        ->exists();

                    if ($hasPaidOrder) {
                        Enrollment::firstOrCreate(
                            ['user_id' => $user->id, 'course_id' => $lesson->course_id],
                            ['status' => 'active', 'enrolled_at' => now()]
                        );
                    } else {
                        throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
                    }
                }
            }
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

        // 2. Kiểm tra learner có enrollment active/completed (Tự động cấp quyền nếu đang học bài)
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $lesson->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            $order = Order::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $lesson->course_id],
                [
                    'order_code' => 'ORD-' . strtoupper(uniqid()),
                    'amount' => $course->sale_price ?? $course->price ?? 0,
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]
            );

            $enrollment = Enrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $lesson->course_id],
                [
                    'order_id' => $order->id,
                    'status' => 'active',
                    'enrolled_at' => now(),
                    'progress_percent' => 0,
                ]
            );
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

        // 5. Tự động quét kiểm duyệt nội dung (Auto Moderation)
        $modResult = $this->moderatorService->inspect($data['content']);
        $initialStatus = $modResult['suggested_status'] ?? 'visible';

        // 6. Thêm comment mới
        $comment = Comment::create([
            'parent_id' => $parentId,
            'user_id' => $user->id,
            'order_id' => $order ? $order->id : null,
            'lesson_id' => $lessonId,
            'content' => $data['content'],
            'status' => $initialStatus,
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

        // 3. Tự động quét kiểm duyệt nội dung phản hồi (Auto Moderation)
        $modResult = $this->moderatorService->inspect($data['content']);
        $initialStatus = $modResult['suggested_status'] ?? 'visible';

        // 4. Tạo bình luận phản hồi
        $reply = Comment::create([
            'parent_id' => $parentComment->id,
            'user_id' => $user->id,
            'order_id' => null,
            'lesson_id' => $parentComment->lesson_id,
            'content' => $data['content'],
            'status' => $initialStatus,
        ]);

        return $reply->load('user');
    }
}
