<?php
namespace App\Repositories\Interaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
final class InstructorQuestionRepository
{
    public function summary(int $instructorId, array $filters = []): array
    {
        $baseQuery = $this->rootQuestionsBaseQuery($instructorId, $filters);
        $total = (clone $baseQuery)->count('q.id');
        $answered = (clone $baseQuery)
            ->whereExists(fn (Builder $query) => $this->answeredExistsQuery($query, $instructorId))
            ->count('q.id');
        return [
            'total_questions' => $total,
            'answered_questions' => $answered,
            'unanswered_questions' => max(0, $total - $answered),
        ];
    }
    public function paginateQuestions(int $instructorId, array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);
        $status = $filters['status'] ?? 'all';
        $sort = $filters['sort'] ?? 'newest';
        $query = $this->rootQuestionsBaseQuery($instructorId, $filters)
            ->select($this->questionSelectColumns($instructorId));
        if ($status === 'answered') {
            $query->whereExists(fn (Builder $subQuery) => $this->answeredExistsQuery($subQuery, $instructorId));
        }
        if ($status === 'unanswered') {
            $query->whereNotExists(fn (Builder $subQuery) => $this->answeredExistsQuery($subQuery, $instructorId));
        }
        $query->orderBy('q.created_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->orderBy('q.id', $sort === 'oldest' ? 'asc' : 'desc');
        return $query->paginate($perPage);
    }
    public function findQuestionDetail(int $questionId, int $instructorId): ?array
    {
        $question = $this->rootQuestionsBaseQuery($instructorId, [])
            ->where('q.id', $questionId)
            ->select($this->questionSelectColumns($instructorId))
            ->first();
        if ($question === null) {
            return null;
        }
        $replies = $this->getVisibleReplies($questionId, $instructorId);
        return [
            'question' => $question,
            'replies' => $replies,
            'is_answered' => (bool) $question->is_answered,
        ];
    }
    public function findCommentWithContext(int $commentId): ?stdClass
    {
        return DB::table('comments as c')
            ->join('users as comment_user', 'comment_user.id', '=', 'c.user_id')
            ->join('lessons', 'lessons.id', '=', 'c.lesson_id')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->where('c.id', $commentId)
            ->select([
                'c.id',
                'c.parent_id',
                'c.user_id',
                'c.lesson_id',
                'c.content',
                'c.status',
                'c.created_at',
                'c.updated_at',
                'comment_user.role as comment_user_role',
                'courses.id as course_id',
                'courses.instructor_id as course_instructor_id',
            ])
            ->first();
    }
    public function createReply(int $questionId, int $instructorId, int $lessonId, string $content): stdClass
    {
        $now = now();
        $replyId = DB::table('comments')->insertGetId([
            'parent_id' => $questionId,
            'user_id' => $instructorId,
            'order_id' => null,
            'lesson_id' => $lessonId,
            'content' => $content,
            'status' => 'visible',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return $this->findReplyById($replyId, $instructorId);
    }
    public function findReplyById(int $replyId, int $instructorId): stdClass
    {
        return DB::table('comments as replies')
            ->join('users as reply_user', 'reply_user.id', '=', 'replies.user_id')
            ->where('replies.id', $replyId)
            ->select([
                'replies.id',
                'replies.parent_id',
                'replies.user_id',
                'replies.lesson_id',
                'replies.content',
                'replies.status',
                'replies.created_at',
                'replies.updated_at',
                'reply_user.full_name as user_full_name',
                'reply_user.role as user_role',
                DB::raw('CASE WHEN replies.user_id = ' . (int) $instructorId . ' THEN 1 ELSE 0 END as is_instructor_reply'),
            ])
            ->first();
    }
    public function getCourseOptions(int $instructorId): Collection
    {
        return DB::table('courses')
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->orderBy('title')
            ->select(['id', 'title', 'status'])
            ->get();
    }
    public function getLessonOptions(int $instructorId, array $filters = []): Collection
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 100), 1), 100);
        $page = max((int) ($filters['page'] ?? 1), 1);
        $query = DB::table('lessons')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->leftJoin('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('courses.deleted_at')
            ->whereNull('lessons.deleted_at');
        if (!empty($filters['course_id'])) {
            $query->where('courses.id', (int) $filters['course_id']);
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('lessons.title', 'like', '%' . $search . '%');
        }
        return $query
            ->orderBy('courses.title')
            ->orderBy('course_sections.sort_order')
            ->orderBy('lessons.sort_order')
            ->select([
                'lessons.id',
                'lessons.title',
                'lessons.course_id',
                'courses.title as course_title',
                'course_sections.id as section_id',
                'course_sections.title as section_title',
            ])
            ->forPage($page, $perPage)
            ->get();
    }
    public function courseOwnedByInstructor(int $courseId, int $instructorId): bool
    {
        return DB::table('courses')
            ->where('id', $courseId)
            ->where('instructor_id', $instructorId)
            ->whereNull('deleted_at')
            ->exists();
    }
    public function lessonOwnedByInstructor(int $lessonId, int $instructorId, ?int $courseId = null): bool
    {
        $query = DB::table('lessons')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->where('lessons.id', $lessonId)
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('lessons.deleted_at')
            ->whereNull('courses.deleted_at');
        if ($courseId !== null) {
            $query->where('courses.id', $courseId);
        }
        return $query->exists();
    }
    private function rootQuestionsBaseQuery(int $instructorId, array $filters = []): Builder
    {
        $query = DB::table('comments as q')
            ->join('users as learner', 'learner.id', '=', 'q.user_id')
            ->join('lessons', 'lessons.id', '=', 'q.lesson_id')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->whereNull('q.parent_id')
            ->where('q.status', 'visible')
            ->where('learner.role', 'learner')
            ->where('courses.instructor_id', $instructorId)
            ->whereNull('lessons.deleted_at')
            ->whereNull('courses.deleted_at');
        if (!empty($filters['course_id'])) {
            $query->where('courses.id', (int) $filters['course_id']);
        }
        if (!empty($filters['lesson_id'])) {
            $query->where('lessons.id', (int) $filters['lesson_id']);
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('q.content', 'like', '%' . $search . '%');
        }
        return $query;
    }
    private function answeredExistsQuery(Builder $query, int $instructorId): void
    {
        $query->select(DB::raw(1))
            ->from('comments as instructor_replies')
            ->whereColumn('instructor_replies.parent_id', 'q.id')
            ->where('instructor_replies.user_id', $instructorId)
            ->where('instructor_replies.status', 'visible');
    }
    private function getVisibleReplies(int $questionId, int $instructorId): Collection
    {
        return DB::table('comments as replies')
            ->join('users as reply_user', 'reply_user.id', '=', 'replies.user_id')
            ->where('replies.parent_id', $questionId)
            ->where('replies.status', 'visible')
            ->orderBy('replies.created_at')
            ->orderBy('replies.id')
            ->select([
                'replies.id',
                'replies.parent_id',
                'replies.user_id',
                'replies.lesson_id',
                'replies.content',
                'replies.status',
                'replies.created_at',
                'replies.updated_at',
                'reply_user.full_name as user_full_name',
                'reply_user.role as user_role',
                DB::raw('CASE WHEN replies.user_id = ' . (int) $instructorId . ' THEN 1 ELSE 0 END as is_instructor_reply'),
            ])
            ->get();
    }
    private function questionSelectColumns(int $instructorId): array
    {
        return [
            'q.id as comment_id',
            'q.content',
            'q.created_at',
            'q.updated_at',
            'learner.id as learner_id',
            'learner.full_name as learner_full_name',
            'learner.email as learner_email',
            'lessons.id as lesson_id',
            'lessons.title as lesson_title',
            'lessons.slug as lesson_slug',
            'courses.id as course_id',
            'courses.title as course_title',
            'courses.slug as course_slug',
            DB::raw('(EXISTS (
                SELECT 1
                FROM comments as instructor_replies
                WHERE instructor_replies.parent_id = q.id
                AND instructor_replies.user_id = ' . (int) $instructorId . '
                AND instructor_replies.status = "visible"
            )) as is_answered'),
            DB::raw('(SELECT COUNT(*)
                FROM comments as visible_replies
                WHERE visible_replies.parent_id = q.id
                AND visible_replies.status = "visible"
            ) as reply_count'),
        ];
    }
}