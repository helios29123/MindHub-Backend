<?php

namespace App\Services\Interaction;

use App\Models\Comment;
use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Interaction\InstructorQuestionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\DB;

class InstructorQuestionService
{
    public function __construct(
        private readonly InstructorQuestionRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
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

        return [
            'total_questions' => $total,
            'answered_questions' => $answered,
            'unanswered_questions' => $unanswered,
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
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Bạn không được trả lời Q&A của khóa học này.');
        }

        if ($lesson->status !== 'published' || $lesson->course->status !== 'published') {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Nội dung chưa khả dụng.');
        }

        $content = trim($data['content'] ?? '');
        if ($content === '') {
            throw new UnprocessableEntityHttpException('Nội dung không được để trống.');
        }

        $reply = Comment::create([
            'parent_id' => $comment->id,
            'user_id' => $instructorId,
            'lesson_id' => $comment->lesson_id,
            'content' => $content,
            'status' => 'visible',
        ]);

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
