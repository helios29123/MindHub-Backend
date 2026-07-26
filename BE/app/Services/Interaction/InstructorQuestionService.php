<?php

namespace App\Services\Interaction;

use App\Models\Comment;
use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Interaction\InstructorQuestionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\DB;

class InstructorQuestionService
{
    public function __construct(
        private readonly InstructorQuestionRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
    }

    public function sanitizeHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }
        // Remove script tags and contents
        $sanitized = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $html);
        // Remove javascript: URLs
        $sanitized = preg_replace('/javascript:[^\s"\'<>]+/i', '', $sanitized);
        // Remove dangerous event attributes (onerror, onload, onclick...)
        $sanitized = preg_replace('/\s+on\w+\s*=\s*(["\']).*?\1/i', '', $sanitized);
        $sanitized = preg_replace('/\s+on\w+\s*=\s*[^>\s]+/i', '', $sanitized);
        return trim($sanitized);
    }

    public function paginateQuestions(int $instructorId, array $filters): LengthAwarePaginator
    {
        if (!empty($filters['course_id']) && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new UnprocessableEntityHttpException('Dữ liệu không hợp lệ.');
        }
        if (!empty($filters['lesson_id'])) {
            $lesson = DB::table('lessons')
                ->join('courses', 'courses.id', '=', 'lessons.course_id')
                ->where('lessons.id', $filters['lesson_id'])
                ->where('courses.instructor_id', $instructorId)
                ->whereNull('courses.deleted_at')
                ->exists();
            if (!$lesson) {
                throw new UnprocessableEntityHttpException('Bài học không hợp lệ.');
            }
        }

        return $this->repository->paginateQuestions($instructorId, $filters);
    }

    public function getQuestionSummary(int $instructorId, array $filters): array
    {
        $courseId = $filters['course_id'] ?? null;
        $lessonId = $filters['lesson_id'] ?? null;

        if ($courseId && !$this->courseRepository->instructorOwnsCourse($instructorId, (int) $courseId)) {
            throw new UnprocessableEntityHttpException('Khoá học không hợp lệ.');
        }

        if ($lessonId) {
            $lessonQuery = DB::table('lessons')
                ->join('courses', 'courses.id', '=', 'lessons.course_id')
                ->where('lessons.id', $lessonId)
                ->where('courses.instructor_id', $instructorId)
                ->whereNull('courses.deleted_at');
            if ($courseId) {
                $lessonQuery->where('lessons.course_id', $courseId);
            }
            if (!$lessonQuery->exists()) {
                throw new UnprocessableEntityHttpException('Bài học không hợp lệ.');
            }
        }

        $query = DB::table('comments as q')
            ->join('lessons', 'lessons.id', '=', 'q.lesson_id')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->join('users as learner', 'learner.id', '=', 'q.user_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->whereNull('q.parent_id')
            ->where('q.status', 'visible')
            ->where('learner.role', 'learner');

        if ($courseId) {
            $query->where('courses.id', $courseId);
        }
        if ($lessonId) {
            $query->where('lessons.id', $lessonId);
        }

        $total = $query->count();

        // Answered: has at least one reply from the instructor
        $answeredQuery = clone $query;
        $answered = $answeredQuery->whereExists(function ($sub) use ($instructorId) {
            $sub->select(DB::raw(1))
                ->from('comments as r')
                ->whereColumn('r.parent_id', 'q.id')
                ->where('r.user_id', $instructorId)
                ->where('r.status', 'visible');
        })->count();

        $unanswered = $total - $answered;

        // Comments today: Questions and Replies created today for this instructor's courses
        $commentsToday = DB::table('comments as c')
            ->join('lessons', 'lessons.id', '=', 'c.lesson_id')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->where('c.status', 'visible')
            ->whereDate('c.created_at', now()->toDateString())
            ->count();

        // Starred count for instructor
        $starredCount = SchemaHasTableCheck('instructor_question_stars') 
            ? DB::table('instructor_question_stars')->where('instructor_id', $instructorId)->count()
            : 0;

        return [
            'total_questions' => $total,
            'answered_questions' => $answered,
            'unanswered_questions' => $unanswered,
            'comments_today' => $commentsToday,
            'starred' => $starredCount,
        ];
    }

    public function getQuestionDetails(int $instructorId, int $commentId): Comment
    {
        $comment = Comment::where('id', $commentId)
            ->whereNull('parent_id')
            ->where('status', 'visible')
            ->first();

        if (!$comment) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        $lesson = $comment->lesson;
        if (!$lesson || !$lesson->course || (int) $lesson->course->instructor_id !== $instructorId) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        if ($lesson->course->deleted_at !== null) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        return $comment;
    }

    public function starQuestion(int $instructorId, int $commentId): array
    {
        $comment = $this->getQuestionDetails($instructorId, $commentId);
        
        DB::table('instructor_question_stars')->insertOrIgnore([
            'instructor_id' => $instructorId,
            'comment_id' => $commentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['starred' => true, 'question_id' => $commentId];
    }

    public function unstarQuestion(int $instructorId, int $commentId): array
    {
        $comment = $this->getQuestionDetails($instructorId, $commentId);

        DB::table('instructor_question_stars')
            ->where('instructor_id', $instructorId)
            ->where('comment_id', $commentId)
            ->delete();

        return ['starred' => false, 'question_id' => $commentId];
    }

    public function replyToQuestion(int $instructorId, int $commentId, array $data): array
    {
        $comment = Comment::where('id', $commentId)->first();
        if (!$comment) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        if ($comment->status !== 'visible' || $comment->deleted_at !== null) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        if ($comment->parent_id !== null) {
            throw new UnprocessableEntityHttpException('Không thể trả lời một phản hồi.');
        }

        $rootUser = $comment->user;
        if ($rootUser && in_array($rootUser->role, ['instructor', 'admin'])) {
            throw new UnprocessableEntityHttpException('Không thể trả lời bình luận của giảng viên/admin.');
        }

        $lesson = $comment->lesson;
        if (!$lesson || !$lesson->course || $lesson->course->deleted_at !== null) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        if ((int) $lesson->course->instructor_id !== $instructorId) {
            throw new HttpException(403, 'Bạn không được trả lời Q&A của khóa học này.');
        }

        if ($lesson->status !== 'published' || $lesson->course->status !== 'published') {
            throw new HttpException(403, 'Nội dung chưa khả dụng.');
        }

        $rawContent = trim($data['content'] ?? '');
        if ($rawContent === '') {
            throw new UnprocessableEntityHttpException('Nội dung không được để trống.');
        }

        $sanitizedContent = $this->sanitizeHtml($rawContent);
        $isOfficial = isset($data['is_official']) ? (bool)$data['is_official'] : true;
        $notifyLearner = isset($data['notify_learner']) ? (bool)$data['notify_learner'] : true;

        $reply = Comment::create([
            'parent_id' => $comment->id,
            'user_id' => $instructorId,
            'lesson_id' => $comment->lesson_id,
            'content' => $sanitizedContent,
            'status' => 'visible',
            'is_official' => $isOfficial,
        ]);

        // Trigger Notification if requested
        if ($notifyLearner && $comment->user_id) {
            // Check for recent duplicate within 5 seconds to prevent double click duplicates
            $recent = DB::table('notifications')
                ->where('user_id', $comment->user_id)
                ->where('type', 'question_reply')
                ->where('created_at', '>=', now()->subSeconds(5))
                ->exists();

            if (!$recent) {
                DB::table('notifications')->insert([
                    'user_id' => $comment->user_id,
                    'type' => 'question_reply',
                    'title' => 'Giảng viên đã trả lời câu hỏi của bạn',
                    'message' => 'Giảng viên đã trả lời câu hỏi trong bài học: ' . ($lesson->title ?? 'Bài học'),
                    'data' => json_encode(['question_id' => $comment->id, 'reply_id' => $reply->id]),
                    'action_url' => '/lessons/' . $comment->lesson_id . '?question_id=' . $comment->id,
                    'channel' => 'database',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $answerCount = Comment::where('parent_id', $comment->id)
            ->where('user_id', $instructorId)
            ->where('status', 'visible')
            ->count();

        return [
            'reply' => $reply,
            'question_status' => [
                'is_answered' => $answerCount > 0,
                'answer_count' => $answerCount,
                'status' => $answerCount > 0 ? 'answered' : 'unanswered',
                'status_label' => $answerCount > 0 ? 'Đã trả lời' : 'Chưa trả lời',
            ]
        ];
    }

    public function updateReply(int $instructorId, int $questionId, int $replyId, array $data): Comment
    {
        $reply = Comment::where('id', $replyId)
            ->where('parent_id', $questionId)
            ->where('status', 'visible')
            ->first();

        if (!$reply) {
            throw new NotFoundHttpException('Không tìm thấy câu trả lời.');
        }

        if ((int) $reply->user_id !== $instructorId) {
            throw new HttpException(403, 'Bạn không có quyền sửa câu trả lời này.');
        }

        $rawContent = trim($data['content'] ?? '');
        if ($rawContent === '') {
            throw new UnprocessableEntityHttpException('Nội dung không được để trống.');
        }

        $reply->content = $this->sanitizeHtml($rawContent);
        if (isset($data['is_official'])) {
            $reply->is_official = (bool)$data['is_official'];
        }
        $reply->save();

        return $reply;
    }

    public function deleteReply(int $instructorId, int $questionId, int $replyId): array
    {
        $reply = Comment::where('id', $replyId)
            ->where('parent_id', $questionId)
            ->first();

        if (!$reply) {
            throw new NotFoundHttpException('Không tìm thấy câu trả lời.');
        }

        if ((int) $reply->user_id !== $instructorId) {
            throw new HttpException(403, 'Bạn không có quyền xóa câu trả lời này.');
        }

        $reply->status = 'deleted';
        $reply->save();

        $remainingCount = Comment::where('parent_id', $questionId)
            ->where('user_id', $instructorId)
            ->where('status', 'visible')
            ->count();

        return [
            'deleted' => true,
            'remaining_answers' => $remainingCount,
            'question_status' => $remainingCount > 0 ? 'answered' : 'unanswered',
        ];
    }

    public function updateQuestionStatus(int $instructorId, int $commentId, string $status): Comment
    {
        $comment = $this->getQuestionIncludingHidden($instructorId, $commentId);
        
        if (!in_array($status, ['answered', 'unanswered', 'hidden', 'visible'])) {
            throw new UnprocessableEntityHttpException('Trạng thái không hợp lệ.');
        }

        if ($status === 'hidden' || $status === 'visible') {
            $comment->status = $status;
        }

        $comment->save();
        return $comment;
    }

    public function hideQuestion(int $instructorId, int $commentId): Comment
    {
        $comment = $this->getQuestionIncludingHidden($instructorId, $commentId);
        $comment->status = 'hidden';
        $comment->save();
        return $comment;
    }

    public function showQuestion(int $instructorId, int $commentId): Comment
    {
        $comment = $this->getQuestionIncludingHidden($instructorId, $commentId);
        $comment->status = 'visible';
        $comment->save();
        return $comment;
    }

    public function deleteQuestion(int $instructorId, int $commentId): Comment
    {
        $comment = $this->getQuestionIncludingHidden($instructorId, $commentId);
        $comment->status = 'deleted';
        $comment->save();
        return $comment;
    }

    private function getQuestionIncludingHidden(int $instructorId, int $commentId): Comment
    {
        $comment = Comment::where('id', $commentId)
            ->whereNull('parent_id')
            ->whereIn('status', ['visible', 'hidden', 'deleted'])
            ->first();

        if (!$comment) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        $lesson = $comment->lesson;
        if (!$lesson || !$lesson->course || (int) $lesson->course->instructor_id !== $instructorId) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        if ($lesson->course->deleted_at !== null) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        return $comment;
    }
}

function SchemaHasTableCheck(string $table): bool {
    try {
        return \Illuminate\Support\Facades\Schema::hasTable($table);
    } catch (\Throwable $e) {
        return false;
    }
}
