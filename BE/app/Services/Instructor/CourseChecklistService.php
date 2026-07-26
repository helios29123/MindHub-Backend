<?php

namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Repositories\Instructor\InstructorCourseRepository;
use Illuminate\Support\Collection;

class CourseChecklistService
{
    public function __construct(
        private readonly InstructorCourseRepository $instructorCourseRepository
    ) {
    }

    public function getChecklist(int $instructorId, int $courseId, array $filters = []): array
    {
        $strict = (bool) ($filters['strict'] ?? false);

        $course = $this->instructorCourseRepository->findCourseForChecklist($courseId);

        if (! $course) {
            throw new BusinessException('Không tìm thấy khóa học.', 404);
        }

        if ((int) $course->instructor_id !== $instructorId) {
            throw new BusinessException('Bạn không có quyền xem checklist khóa học này.', 403);
        }

        $categories = $this->instructorCourseRepository->getChecklistCategories($courseId);
        $sections = $this->instructorCourseRepository->getChecklistSections($courseId);
        $lessons = $this->instructorCourseRepository->getChecklistLessons($courseId);
        $assetCount = $this->instructorCourseRepository->countChecklistLessonAssets($courseId);
        $quizzes = $this->instructorCourseRepository->getChecklistQuizzes($courseId);
        $questionStats = $this->instructorCourseRepository->getChecklistQuizQuestionStats($courseId);

        $missingItems = [];
        $warnings = [];
        $checks = [];

        $this->checkCourseInfo($course, $checks, $missingItems, $warnings, $strict);
        $this->checkCategories($categories, $checks, $missingItems, $warnings);
        $this->checkSections($sections, $checks, $missingItems, $warnings);
        $this->checkLessons($lessons, $assetCount, $checks, $missingItems, $warnings, $strict);
        $this->checkQuizzes($quizzes, $questionStats, $checks, $missingItems, $warnings);

        $missingItems = array_values(array_unique($missingItems));
        $warnings = array_values(array_unique($warnings));

        return [
            'course_id' => (int) $course->id,
            'course_title' => $course->title,
            'course_status' => $course->status,
            'strict' => $strict,
            'passed' => count($missingItems) === 0,
            'missing_items' => $missingItems,
            'warnings' => $warnings,
            'summary' => [
                'categories_count' => $categories->count(),
                'sections_count' => $sections->count(),
                'published_sections_count' => $sections->where('status', 'published')->count(),
                'lessons_count' => $lessons->count(),
                'published_lessons_count' => $lessons->where('status', 'published')->count(),
                'lesson_assets_count' => $assetCount,
                'quizzes_count' => $quizzes->count(),
                'published_quizzes_count' => $quizzes->where('status', 'published')->count(),
                'quiz_questions_count' => $questionStats->count(),
            ],
            'checks' => $checks,
        ];
    }

    private function checkCourseInfo(object $course, array &$checks, array &$missingItems, array &$warnings, bool $strict): void
    {
        $missing = [];
        $checkWarnings = [];

        if ($this->blank($course->title)) {
            $missing[] = 'course_title';
        }

        if ($this->blank($course->description)) {
            $missing[] = 'course_description';
        }

        if ($this->blank($course->thumbnail_url)) {
            $missing[] = 'course_thumbnail';
        }

        if ($course->price === null || (float) $course->price < 0) {
            $missing[] = 'course_price';
        }

        if ($this->blank($course->short_description)) {
            $checkWarnings[] = 'course_short_description';
        }

        if ($this->blank($course->intro_video_url)) {
            $checkWarnings[] = 'course_intro_video';
        }

        if ($strict && $this->blank($course->intro_video_url)) {
            $missing[] = 'course_intro_video';
        }

        $this->pushCheck(
            $checks,
            $missingItems,
            $warnings,
            'course_basic_info',
            'Thﾃｴng tin cﾆ｡ b蘯｣n c盻ｧa khﾃｳa h盻皇',
            $missing,
            $checkWarnings
        );
    }

    private function checkCategories(Collection $categories, array &$checks, array &$missingItems, array &$warnings): void
    {
        $missing = [];

        if ($categories->isEmpty()) {
            $missing[] = 'course_category';
        }

        $this->pushCheck(
            $checks,
            $missingItems,
            $warnings,
            'course_category',
            'Danh mục khóa học',
            $missing,
            []
        );
    }

    private function checkSections(Collection $sections, array &$checks, array &$missingItems, array &$warnings): void
    {
        $missing = [];
        $checkWarnings = [];

        if ($sections->isEmpty()) {
            $missing[] = 'course_section';
        }

        if ($sections->where('status', 'published')->isEmpty()) {
            $missing[] = 'published_section';
        }

        if ($sections->where('status', '!=', 'published')->isNotEmpty()) {
            $checkWarnings[] = 'draft_or_hidden_section';
        }

        $this->pushCheck(
            $checks,
            $missingItems,
            $warnings,
            'course_section',
            'Section chứa khóa học',
            $missing,
            $checkWarnings
        );
    }

    private function checkLessons(
        Collection $lessons,
        int $assetCount,
        array &$checks,
        array &$missingItems,
        array &$warnings,
        bool $strict
    ): void {
        $missing = [];
        $checkWarnings = [];

        $publishedLessons = $lessons->where('status', 'published');

        if ($lessons->isEmpty()) {
            $missing[] = 'lesson';
        }

        if ($publishedLessons->isEmpty()) {
            $missing[] = 'published_lesson';
        }

        foreach ($publishedLessons as $lesson) {
            $lessonType = strtolower((string) $lesson->lesson_type);

            if ($lessonType === 'video') {
                $videoUrl = (string) ($lesson->video_url ?? '');
                $isInvalidVideo = $this->blank($videoUrl) || str_starts_with($videoUrl, 'blob:');
                if ($isInvalidVideo) {
                    $missing[] = "Bài học '{$lesson->title}' chưa có video được tải lên hợp lệ.";
                }

                if (((int) ($lesson->video_duration_seconds ?? 0)) <= 0) {
                    $checkWarnings[] = 'lesson_video_duration';
                }
            }

            if (in_array($lessonType, ['text', 'article', 'document'], true) && $this->blank($lesson->content)) {
                $missing[] = 'lesson_content';
            }

            if (! in_array($lessonType, ['video', 'text', 'article', 'document', 'quiz'], true)
                && $this->blank($lesson->content)
                && $this->blank($lesson->video_url)
            ) {
                $missing[] = 'lesson_content';
            }
        }

        if ($lessons->where('status', '!=', 'published')->isNotEmpty()) {
            $checkWarnings[] = 'draft_or_hidden_lesson';
        }

        if ($assetCount === 0) {
            $checkWarnings[] = 'lesson_asset';
        }

        if ($strict && $assetCount === 0) {
            $missing[] = 'lesson_asset';
        }

        $this->pushCheck(
            $checks,
            $missingItems,
            $warnings,
            'lesson_content',
            'Bﾃi h盻皇 vﾃ n盻冓 dung bﾃi h盻皇',
            $missing,
            $checkWarnings
        );
    }

    private function checkQuizzes(
        Collection $quizzes,
        Collection $questionStats,
        array &$checks,
        array &$missingItems,
        array &$warnings
    ): void {
        $missing = [];
        $checkWarnings = [];

        $publishedQuizzes = $quizzes->where('status', 'published');

        if ($publishedQuizzes->isEmpty()) {
            $missing[] = 'quiz';
        }

        if ($questionStats->isEmpty()) {
            $missing[] = 'quiz_question';
        }

        foreach ($questionStats as $question) {
            if ($this->blank($question->question_text)) {
                $missing[] = 'quiz_question_text';
            }

            $questionType = strtolower((string) $question->question_type);

            if (in_array($questionType, ['single_choice', 'multiple_choice', 'choice'], true)) {
                if ((int) $question->options_count < 2) {
                    $missing[] = 'quiz_option';
                }

                if ((int) $question->correct_options_count < 1) {
                    $missing[] = 'quiz_correct_option';
                }
            }
        }

        if ($quizzes->where('status', '!=', 'published')->isNotEmpty()) {
            $checkWarnings[] = 'draft_or_hidden_quiz';
        }

        $this->pushCheck(
            $checks,
            $missingItems,
            $warnings,
            'quiz',
            'Quiz vﾃ cﾃ｢u h盻淑 quiz',
            $missing,
            $checkWarnings
        );
    }

    private function pushCheck(
        array &$checks,
        array &$missingItems,
        array &$warnings,
        string $key,
        string $label,
        array $missing,
        array $checkWarnings
    ): void {
        $missing = array_values(array_unique($missing));
        $checkWarnings = array_values(array_unique($checkWarnings));

        foreach ($missing as $item) {
            $missingItems[] = $item;
        }

        foreach ($checkWarnings as $warning) {
            $warnings[] = $warning;
        }

        $checks[] = [
            'key' => $key,
            'label' => $label,
            'passed' => count($missing) === 0,
            'missing_items' => $missing,
            'warnings' => $checkWarnings,
        ];
    }

    private function blank(mixed $value): bool
    {
        return trim((string) $value) === '';
    }
}
