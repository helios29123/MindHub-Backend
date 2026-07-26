<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
final class InstructorQuestionApiTest extends TestCase
{
    use DatabaseTransactions;
    private User $admin;
    private User $instructor;
    private User $otherInstructor;
    private User $learner;
    private int $courseId;
    private int $secondCourseId;
    private int $otherCourseId;
    private int $deletedCourseId;
    private int $lessonId;
    private int $secondLessonId;
    private int $otherLessonId;
    private int $deletedLessonId;
    private int $answeredQuestionId;
    private int $unansweredQuestionId;
    private int $learnerReplyOnlyQuestionId;
    private int $hiddenInstructorReplyQuestionId;
    private int $otherInstructorReplyOnlyQuestionId;
    private int $secondCourseQuestionId;
    private int $hiddenQuestionId;
    private int $instructorRootQuestionId;
    private int $adminRootQuestionId;
    private int $otherInstructorQuestionId;
    private int $deletedCourseQuestionId;
    private int $instructorReplyId;
    private int $learnerReplyId;
    private int $hiddenInstructorReplyId;
    private int $otherInstructorReplyId;
    private int $commentSequence = 1;
    protected function setUp(): void
    {
        parent::setUp();
        /*
         * Project dùng auth.session custom nên test API ở đây tắt middleware,
         * sau đó dùng actingAs() để request->user() có user hiện tại.
         */
        $this->withoutMiddleware();
        $suffix = str_replace('.', '-', uniqid('qa_', true));
        $this->admin = $this->createUser(
            fullName: 'QA Admin',
            email: 'qa-admin-' . $suffix . '@mindhub.test',
            role: 'admin'
        );
        $this->instructor = $this->createUser(
            fullName: 'QA Instructor',
            email: 'qa-instructor-' . $suffix . '@mindhub.test',
            role: 'instructor'
        );
        $this->otherInstructor = $this->createUser(
            fullName: 'Other QA Instructor',
            email: 'qa-other-instructor-' . $suffix . '@mindhub.test',
            role: 'instructor'
        );
        $this->learner = $this->createUser(
            fullName: 'QA Learner',
            email: 'qa-learner-' . $suffix . '@mindhub.test',
            role: 'learner'
        );
        [$this->courseId, $this->lessonId] = $this->createCourseWithLesson(
            instructorId: (int) $this->instructor->id,
            title: 'QA Laravel Course ' . $suffix,
            slug: 'qa-laravel-course-' . $suffix,
            lessonTitle: 'QA Middleware Lesson',
            lessonSlug: 'qa-middleware-lesson-' . $suffix
        );
        [$this->secondCourseId, $this->secondLessonId] = $this->createCourseWithLesson(
            instructorId: (int) $this->instructor->id,
            title: 'QA PHP Course ' . $suffix,
            slug: 'qa-php-course-' . $suffix,
            lessonTitle: 'QA PHP Lesson',
            lessonSlug: 'qa-php-lesson-' . $suffix
        );
        [$this->otherCourseId, $this->otherLessonId] = $this->createCourseWithLesson(
            instructorId: (int) $this->otherInstructor->id,
            title: 'Other QA Course ' . $suffix,
            slug: 'other-qa-course-' . $suffix,
            lessonTitle: 'Other QA Lesson',
            lessonSlug: 'other-qa-lesson-' . $suffix
        );
        [$this->deletedCourseId, $this->deletedLessonId] = $this->createCourseWithLesson(
            instructorId: (int) $this->instructor->id,
            title: 'Deleted QA Course ' . $suffix,
            slug: 'deleted-qa-course-' . $suffix,
            lessonTitle: 'Deleted QA Lesson',
            lessonSlug: 'deleted-qa-lesson-' . $suffix,
            deletedAt: now()
        );
        /*
         * Các root question hợp lệ của instructor hiện tại:
         * 1 answeredQuestionId                  => answered
         * 2 unansweredQuestionId                => unanswered
         * 3 learnerReplyOnlyQuestionId          => unanswered vì chỉ learner reply
         * 4 hiddenInstructorReplyQuestionId     => unanswered vì instructor reply bị hidden
         * 5 otherInstructorReplyOnlyQuestionId  => unanswered vì reply không phải instructor hiện tại
         * 6 secondCourseQuestionId              => unanswered
         *
         * Total = 6
         * Answered = 1
         * Unanswered = 5
         */
        $this->answeredQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->learner->id,
            lessonId: $this->lessonId,
            content: 'Câu hỏi đã được giảng viên trả lời?',
            status: 'visible'
        );
        $this->instructorReplyId = $this->createComment(
            parentId: $this->answeredQuestionId,
            userId: (int) $this->instructor->id,
            lessonId: $this->lessonId,
            content: 'Đây là câu trả lời visible của instructor.',
            status: 'visible'
        );
        $this->unansweredQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->learner->id,
            lessonId: $this->lessonId,
            content: 'Câu hỏi chưa trả lời keyword-search?',
            status: 'visible'
        );
        $this->learnerReplyOnlyQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->learner->id,
            lessonId: $this->lessonId,
            content: 'Câu hỏi chỉ có learner reply?',
            status: 'visible'
        );
        $this->learnerReplyId = $this->createComment(
            parentId: $this->learnerReplyOnlyQuestionId,
            userId: (int) $this->learner->id,
            lessonId: $this->lessonId,
            content: 'Learner tự reply nên không tính là instructor đã trả lời.',
            status: 'visible'
        );
        $this->hiddenInstructorReplyQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->learner->id,
            lessonId: $this->lessonId,
            content: 'Câu hỏi có instructor reply hidden?',
            status: 'visible'
        );
        $this->hiddenInstructorReplyId = $this->createComment(
            parentId: $this->hiddenInstructorReplyQuestionId,
            userId: (int) $this->instructor->id,
            lessonId: $this->lessonId,
            content: 'Reply hidden của instructor không được tính.',
            status: 'hidden'
        );
        $this->otherInstructorReplyOnlyQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->learner->id,
            lessonId: $this->lessonId,
            content: 'Câu hỏi chỉ có instructor khác reply?',
            status: 'visible'
        );
        $this->otherInstructorReplyId = $this->createComment(
            parentId: $this->otherInstructorReplyOnlyQuestionId,
            userId: (int) $this->otherInstructor->id,
            lessonId: $this->lessonId,
            content: 'Reply từ instructor khác không tính là đã trả lời.',
            status: 'visible'
        );
        $this->secondCourseQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->learner->id,
            lessonId: $this->secondLessonId,
            content: 'Câu hỏi ở khóa thứ hai của instructor hiện tại.',
            status: 'visible'
        );
        /*
         * Các comment không được tính:
         */
        $this->hiddenQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->learner->id,
            lessonId: $this->lessonId,
            content: 'Câu hỏi hidden không được tính.',
            status: 'hidden'
        );
        $this->instructorRootQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->instructor->id,
            lessonId: $this->lessonId,
            content: 'Root comment của instructor không được tính là câu hỏi learner.',
            status: 'visible'
        );
        $this->adminRootQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->admin->id,
            lessonId: $this->lessonId,
            content: 'Root comment của admin không được tính là câu hỏi learner.',
            status: 'visible'
        );
        $this->otherInstructorQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->learner->id,
            lessonId: $this->otherLessonId,
            content: 'Câu hỏi thuộc instructor khác.',
            status: 'visible'
        );
        $this->deletedCourseQuestionId = $this->createComment(
            parentId: null,
            userId: (int) $this->learner->id,
            lessonId: $this->deletedLessonId,
            content: 'Câu hỏi thuộc course đã soft delete không được tính.',
            status: 'visible'
        );
    }
    public function test_instructor_can_get_question_summary(): void
    {
        $response = $this->actingAs($this->instructor)->getJson('/api/instructor/questions/summary');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_questions', 6)
            ->assertJsonPath('data.answered_questions', 1)
            ->assertJsonPath('data.unanswered_questions', 5);
    }
    public function test_summary_can_filter_by_course_id(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/summary?course_id=' . $this->courseId);
        $response->assertOk()
            ->assertJsonPath('data.total_questions', 5)
            ->assertJsonPath('data.answered_questions', 1)
            ->assertJsonPath('data.unanswered_questions', 4);
    }
    public function test_summary_can_filter_by_lesson_id(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/summary?lesson_id=' . $this->lessonId);
        $response->assertOk()
            ->assertJsonPath('data.total_questions', 5)
            ->assertJsonPath('data.answered_questions', 1)
            ->assertJsonPath('data.unanswered_questions', 4);
    }
    public function test_summary_rejects_other_instructor_course_filter(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/summary?course_id=' . $this->otherCourseId);
        $response->assertUnprocessable();
    }
    public function test_summary_rejects_lesson_that_does_not_belong_to_selected_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/summary?course_id=' . $this->courseId . '&lesson_id=' . $this->secondLessonId);
        $response->assertUnprocessable();
    }
    public function test_instructor_can_list_all_questions(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?status=all&per_page=20');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 6);
    }
    public function test_instructor_can_list_answered_questions(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?status=answered');
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.comment_id', $this->answeredQuestionId)
            ->assertJsonPath('data.0.is_answered', true)
            ->assertJsonPath('data.0.status_label', 'Đã trả lời');
    }
    public function test_instructor_can_list_unanswered_questions(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?status=unanswered&per_page=20');
        $response->assertOk()
            ->assertJsonPath('meta.total', 5);
        $ids = array_column($response->json('data'), 'comment_id');
        $this->assertContains($this->unansweredQuestionId, $ids);
        $this->assertContains($this->learnerReplyOnlyQuestionId, $ids);
        $this->assertContains($this->hiddenInstructorReplyQuestionId, $ids);
        $this->assertContains($this->otherInstructorReplyOnlyQuestionId, $ids);
        $this->assertContains($this->secondCourseQuestionId, $ids);
        $this->assertNotContains($this->answeredQuestionId, $ids);
    }
    public function test_reply_from_learner_does_not_mark_question_as_answered(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?status=all&per_page=20');
        $item = collect($response->json('data'))
            ->firstWhere('comment_id', $this->learnerReplyOnlyQuestionId);
        $this->assertNotNull($item);
        $this->assertFalse($item['is_answered']);
        $this->assertSame('Chưa trả lời', $item['status_label']);
        $this->assertSame(1, $item['reply_count']);
    }
    public function test_hidden_instructor_reply_does_not_mark_question_as_answered(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?status=all&per_page=20');
        $item = collect($response->json('data'))
            ->firstWhere('comment_id', $this->hiddenInstructorReplyQuestionId);
        $this->assertNotNull($item);
        $this->assertFalse($item['is_answered']);
        $this->assertSame('Chưa trả lời', $item['status_label']);
        $this->assertSame(0, $item['reply_count']);
    }
    public function test_reply_from_other_instructor_does_not_mark_question_as_answered(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?status=all&per_page=20');
        $item = collect($response->json('data'))
            ->firstWhere('comment_id', $this->otherInstructorReplyOnlyQuestionId);
        $this->assertNotNull($item);
        $this->assertFalse($item['is_answered']);
        $this->assertSame('Chưa trả lời', $item['status_label']);
        $this->assertSame(1, $item['reply_count']);
    }
    public function test_instructor_can_search_questions_by_content(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?status=unanswered&search=keyword-search');
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.comment_id', $this->unansweredQuestionId);
    }
    public function test_instructor_can_filter_questions_by_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?course_id=' . $this->secondCourseId);
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.comment_id', $this->secondCourseQuestionId);
    }
    public function test_instructor_can_filter_questions_by_lesson(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?lesson_id=' . $this->lessonId . '&per_page=20');
        $response->assertOk()
            ->assertJsonPath('meta.total', 5);
    }
    public function test_instructor_can_sort_questions_newest_first(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?sort=newest&per_page=1');
        $response->assertOk()
            ->assertJsonPath('data.0.comment_id', $this->secondCourseQuestionId);
    }
    public function test_instructor_can_sort_questions_oldest_first(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?sort=oldest&per_page=1');
        $response->assertOk()
            ->assertJsonPath('data.0.comment_id', $this->answeredQuestionId);
    }
    public function test_question_index_rejects_invalid_status(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?status=invalid_status');
        $response->assertUnprocessable();
    }
    public function test_question_index_rejects_other_instructor_lesson_filter(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions?lesson_id=' . $this->otherLessonId);
        $response->assertUnprocessable();
    }
    public function test_instructor_can_show_answered_question_detail(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/' . $this->answeredQuestionId);
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.comment_id', $this->answeredQuestionId)
            ->assertJsonPath('data.is_answered', true)
            ->assertJsonPath('data.status_label', 'Đã trả lời')
            ->assertJsonPath('data.replies.0.id', $this->instructorReplyId)
            ->assertJsonPath('data.replies.0.is_instructor_reply', true);
    }
    public function test_question_detail_includes_visible_learner_replies_but_still_unanswered(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/' . $this->learnerReplyOnlyQuestionId);
        $response->assertOk()
            ->assertJsonPath('data.comment_id', $this->learnerReplyOnlyQuestionId)
            ->assertJsonPath('data.is_answered', false)
            ->assertJsonPath('data.replies.0.id', $this->learnerReplyId)
            ->assertJsonPath('data.replies.0.is_instructor_reply', false);
    }
    public function test_question_detail_excludes_hidden_replies(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/' . $this->hiddenInstructorReplyQuestionId);
        $response->assertOk()
            ->assertJsonPath('data.comment_id', $this->hiddenInstructorReplyQuestionId)
            ->assertJsonPath('data.is_answered', false)
            ->assertJsonCount(0, 'data.replies');
    }
    public function test_instructor_cannot_show_reply_as_question(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/' . $this->instructorReplyId);
        $response->assertNotFound();
    }
    public function test_instructor_cannot_show_hidden_question(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/' . $this->hiddenQuestionId);
        $response->assertNotFound();
    }
    public function test_instructor_cannot_show_other_instructor_question(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/' . $this->otherInstructorQuestionId);
        $response->assertNotFound();
    }
    public function test_instructor_can_reply_question(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/comments/' . $this->unansweredQuestionId . '/replies', [
                'content' => 'Câu trả lời mới của instructor.',
            ]);
        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.reply.parent_id', $this->unansweredQuestionId)
            ->assertJsonPath('data.reply.lesson_id', $this->lessonId)
            ->assertJsonPath('data.question_status.is_answered', true);
        $this->assertDatabaseHas('comments', [
            'parent_id' => $this->unansweredQuestionId,
            'user_id' => $this->instructor->id,
            'lesson_id' => $this->lessonId,
            'content' => 'Câu trả lời mới của instructor.',
            'status' => 'visible',
        ]);
    }
    public function test_reply_content_is_trimmed_before_saving(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/comments/' . $this->unansweredQuestionId . '/replies', [
                'content' => '   Nội dung đã được trim.   ',
            ]);
        $response->assertCreated();
        $this->assertDatabaseHas('comments', [
            'parent_id' => $this->unansweredQuestionId,
            'user_id' => $this->instructor->id,
            'lesson_id' => $this->lessonId,
            'content' => 'Nội dung đã được trim.',
            'status' => 'visible',
        ]);
    }
    public function test_instructor_cannot_reply_to_reply(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/comments/' . $this->instructorReplyId . '/replies', [
                'content' => 'Không được reply vào reply.',
            ]);
        $response->assertUnprocessable();
    }
    public function test_instructor_cannot_reply_hidden_question(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/comments/' . $this->hiddenQuestionId . '/replies', [
                'content' => 'Không được reply hidden question.',
            ]);
        $response->assertNotFound();
    }
    public function test_instructor_cannot_reply_other_instructor_question(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/comments/' . $this->otherInstructorQuestionId . '/replies', [
                'content' => 'Không được reply câu hỏi của instructor khác.',
            ]);
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Bạn không được trả lời Q&A của khóa học này.',
            ]);
    }
    public function test_instructor_cannot_reply_root_comment_created_by_instructor(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/comments/' . $this->instructorRootQuestionId . '/replies', [
                'content' => 'Không được reply root comment của instructor.',
            ]);
        $response->assertUnprocessable();
    }
    public function test_reply_requires_non_empty_content(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/comments/' . $this->unansweredQuestionId . '/replies', [
                'content' => '',
            ]);
        $response->assertUnprocessable();
    }
    public function test_instructor_can_get_course_options(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/course-options');
        $response->assertOk()
            ->assertJsonPath('success', true);
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($this->courseId, $ids);
        $this->assertContains($this->secondCourseId, $ids);
        $this->assertNotContains($this->otherCourseId, $ids);
        $this->assertNotContains($this->deletedCourseId, $ids);
    }
    public function test_instructor_can_get_lesson_options_without_course_filter(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/lesson-options');
        $response->assertOk()
            ->assertJsonPath('success', true);
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($this->lessonId, $ids);
        $this->assertContains($this->secondLessonId, $ids);
        $this->assertNotContains($this->otherLessonId, $ids);
        $this->assertNotContains($this->deletedLessonId, $ids);
    }
    public function test_instructor_can_get_lesson_options_with_course_filter(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/lesson-options?course_id=' . $this->courseId);
        $response->assertOk()
            ->assertJsonPath('success', true);
        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($this->lessonId, $ids);
        $this->assertNotContains($this->secondLessonId, $ids);
        $this->assertNotContains($this->otherLessonId, $ids);
    }
    public function test_lesson_options_reject_other_instructor_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/questions/lesson-options?course_id=' . $this->otherCourseId);
        $response->assertUnprocessable();
    }
    private function createUser(string $fullName, string $email, string $role): User
    {
        return User::query()->create([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => null,
            'role' => $role,
            'status' => 'active',
            'locked' => false,
        ]);
    }
    private function createCourseWithLesson(
        int $instructorId,
        string $title,
        string $slug,
        string $lessonTitle,
        string $lessonSlug,
        mixed $deletedAt = null
    ): array {
        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $instructorId,
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'QA course',
            'description' => 'QA course',
            'price' => 100000,
            'sale_price' => null,
            'level' => 'beginner',
            'language' => 'vi',
            'status' => 'published',
            'is_featured' => false,
            'total_duration_seconds' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => $deletedAt,
        ]);
        $sectionId = DB::table('course_sections')->insertGetId([
            'course_id' => $courseId,
            'title' => 'QA Section for ' . $title,
            'description' => null,
            'sort_order' => 1,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $lessonId = DB::table('lessons')->insertGetId([
            'course_section_id' => $sectionId,
            'course_id' => $courseId,
            'title' => $lessonTitle,
            'slug' => $lessonSlug,
            'lesson_type' => 'text',
            'content' => 'QA content',
            'video_url' => null,
            'video_duration_seconds' => null,
            'is_preview' => false,
            'status' => 'published',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        return [$courseId, $lessonId];
    }
    private function createComment(?int $parentId, int $userId, int $lessonId, string $content, string $status): int
    {
        $time = now()->addSeconds($this->commentSequence++);
        return DB::table('comments')->insertGetId([
            'parent_id' => $parentId,
            'user_id' => $userId,
            'order_id' => null,
            'lesson_id' => $lessonId,
            'content' => $content,
            'status' => $status,
            'created_at' => $time,
            'updated_at' => $time,
        ]);
    }
}