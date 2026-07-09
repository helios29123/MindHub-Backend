<?php
namespace App\Services\Interaction;
use App\Repositories\Interaction\InstructorQuestionRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
final class InstructorQuestionService
{
    public function __construct(
        private readonly InstructorQuestionRepository $questions,
        private readonly DatabaseManager $database
    ) {
    }
    /**
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function getSummary(?object $authUser, array $filters): array
    {
        $instructorId = $this->instructorId($authUser);
        $this->validateOwnedFilters($instructorId, $filters);
        return $this->questions->summary($instructorId, $filters);
    }
    /**
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function paginateQuestions(?object $authUser, array $filters): LengthAwarePaginator
    {
        $instructorId = $this->instructorId($authUser);
        $this->validateOwnedFilters($instructorId, $filters);
        return $this->questions->paginateQuestions($instructorId, $filters);
    }
    /**
     * @throws AuthenticationException
     */
    public function showQuestion(?object $authUser, mixed $questionId): array
    {
        $instructorId = $this->instructorId($authUser);
        $id = $this->normalizeId($questionId, 'Không tìm thấy câu hỏi.');
        $detail = $this->questions->findQuestionDetail($id, $instructorId);
        if ($detail === null) {
            throw new NotFoundHttpException('Không tìm thấy câu hỏi hoặc bạn không có quyền xem.');
        }
        return $detail;
    }
    /**
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function replyQuestion(?object $authUser, mixed $questionId, string $content): array
    {
        $instructorId = $this->instructorId($authUser);
        $id = $this->normalizeId($questionId, 'Không tìm thấy câu hỏi.');
        $question = $this->questions->findCommentWithContext($id);
        if ($question === null) {
            throw new NotFoundHttpException('Không tìm thấy câu hỏi.');
        }
        if ($question->parent_id !== null) {
            throw ValidationException::withMessages([
                'id' => ['Chỉ có thể trả lời câu hỏi gốc.'],
            ]);
        }
        if ($question->status !== 'visible') {
            throw new NotFoundHttpException('Không tìm thấy câu hỏi.');
        }
        if ($question->comment_user_role !== 'learner') {
            throw ValidationException::withMessages([
                'id' => ['Chỉ có thể trả lời câu hỏi của học viên.'],
            ]);
        }
        if ((int) $question->course_instructor_id !== $instructorId) {
            throw new NotFoundHttpException('Không tìm thấy câu hỏi hoặc bạn không có quyền trả lời.');
        }
        $reply = $this->database->transaction(function () use ($question, $instructorId, $content) {
            return $this->questions->createReply(
                questionId: (int) $question->id,
                instructorId: $instructorId,
                lessonId: (int) $question->lesson_id,
                content: trim($content)
            );
        });
        return [
            'reply' => $reply,
            'question_status' => [
                'is_answered' => true,
                'status_label' => 'Đã trả lời',
            ],
        ];
    }
    /**
     * @throws AuthenticationException
     */
    public function getCourseOptions(?object $authUser): Collection
    {
        return $this->questions->getCourseOptions($this->instructorId($authUser));
    }
    /**
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function getLessonOptions(?object $authUser, array $filters): Collection
    {
        $instructorId = $this->instructorId($authUser);
        if (!empty($filters['course_id']) && !$this->questions->courseOwnedByInstructor((int) $filters['course_id'], $instructorId)) {
            throw ValidationException::withMessages([
                'course_id' => ['Không tìm thấy khóa học hoặc bạn không có quyền xem.'],
            ]);
        }
        return $this->questions->getLessonOptions($instructorId, $filters);
    }
    /**
     * @throws AuthenticationException
     */
    private function instructorId(?object $authUser): int
    {
        if ($authUser === null || empty($authUser->id)) {
            throw new AuthenticationException('Unauthenticated.');
        }
        return (int) $authUser->id;
    }
    private function normalizeId(mixed $id, string $message): int
    {
        if (!is_numeric($id) || (int) $id < 1) {
            throw new NotFoundHttpException($message);
        }
        return (int) $id;
    }
    /**
     * @throws ValidationException
     */
    private function validateOwnedFilters(int $instructorId, array $filters): void
    {
        $courseId = !empty($filters['course_id']) ? (int) $filters['course_id'] : null;
        $lessonId = !empty($filters['lesson_id']) ? (int) $filters['lesson_id'] : null;
        if ($courseId !== null && !$this->questions->courseOwnedByInstructor($courseId, $instructorId)) {
            throw ValidationException::withMessages([
                'course_id' => ['Không tìm thấy khóa học hoặc bạn không có quyền xem.'],
            ]);
        }
        if ($lessonId !== null && !$this->questions->lessonOwnedByInstructor($lessonId, $instructorId, $courseId)) {
            throw ValidationException::withMessages([
                'lesson_id' => ['Không tìm thấy bài học hoặc bạn không có quyền xem.'],
            ]);
        }
    }
}