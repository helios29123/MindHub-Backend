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

    public function calculateCompletion(int $instructorId, object|int $courseOrId): array
    {
        $course = is_object($courseOrId) ? $courseOrId : $this->instructorCourseRepository->findCourseForChecklist((int) $courseOrId);
        if (! $course) {
            return [
                'completion_percent' => 0,
                'completed_items' => 0,
                'total_items' => 11,
                'missing_items' => [],
                'next_step' => null,
                'action_label' => 'Bắt đầu cập nhật',
                'passed' => false,
            ];
        }

        $courseId = (int) $course->id;
        $categories = $this->instructorCourseRepository->getChecklistCategories($courseId);
        $sections = $this->instructorCourseRepository->getChecklistSections($courseId);
        $lessons = $this->instructorCourseRepository->getChecklistLessons($courseId);

        $items = [
            [
                'key' => 'title',
                'label' => 'Tên khóa học',
                'passed' => ! $this->blank($course->title),
                'route_step' => 1,
            ],
            [
                'key' => 'short_description',
                'label' => 'Mô tả ngắn khóa học',
                'passed' => ! $this->blank($course->short_description),
                'route_step' => 1,
            ],
            [
                'key' => 'description',
                'label' => 'Mô tả chi tiết khóa học',
                'passed' => ! $this->blank($course->description),
                'route_step' => 1,
            ],
            [
                'key' => 'thumbnail',
                'label' => 'Ảnh đại diện (thumbnail)',
                'passed' => ! $this->blank($course->thumbnail_url),
                'route_step' => 1,
            ],
            [
                'key' => 'category',
                'label' => 'Danh mục khóa học',
                'passed' => $categories->isNotEmpty(),
                'route_step' => 1,
            ],
            [
                'key' => 'price',
                'label' => 'Giá khóa học',
                'passed' => $course->price !== null && (float) $course->price >= 0,
                'route_step' => 1,
            ],
            [
                'key' => 'section',
                'label' => 'Chương học (Section)',
                'passed' => $sections->isNotEmpty(),
                'route_step' => 2,
            ],
            [
                'key' => 'published_section',
                'label' => 'Xuất bản chương học',
                'passed' => $sections->where('status', 'published')->isNotEmpty(),
                'route_step' => 2,
            ],
            [
                'key' => 'lesson',
                'label' => 'Bài học',
                'passed' => $lessons->isNotEmpty(),
                'route_step' => 2,
            ],
            [
                'key' => 'published_lesson',
                'label' => 'Xuất bản bài học',
                'passed' => $lessons->where('status', 'published')->isNotEmpty(),
                'route_step' => 2,
            ],
            [
                'key' => 'lesson_media',
                'label' => 'Nội dung/Video bài học',
                'passed' => $lessons->where('status', 'published')->filter(function ($l) use ($lessons) {
                    $lType = strtolower((string) ($l->lesson_type ?? 'video'));
                    if ($lType === 'video') {
                        $vUrl = (string) ($l->video_url ?? '');
                        return ! $this->blank($vUrl) && ! str_starts_with($vUrl, 'blob:');
                    }
                    return ! $this->blank($l->content ?? '');
                })->isNotEmpty() || $lessons->isEmpty(),
                'route_step' => 2,
            ],
        ];

        $totalItems = count($items);
        $completedItems = 0;
        $missingItems = [];

        foreach ($items as $item) {
            if ($item['passed']) {
                $completedItems++;
            } else {
                $missingItems[] = [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'route_step' => $item['route_step'],
                ];
            }
        }

        $completionPercent = (int) round(($completedItems / $totalItems) * 100);
        $completionPercent = max(0, min(100, $completionPercent));

        $nextStep = null;
        if (! empty($missingItems)) {
            $firstMissing = $missingItems[0];
            $missingKey = $firstMissing['key'];

            $stepName = 'basic-info';
            $routeStep = 1;
            $focusTarget = $missingKey;
            $sectionId = null;
            $lessonId = null;

            if (in_array($missingKey, ['title', 'short_description', 'description', 'category', 'will_learn', 'requirements', 'outcomes'], true)) {
                $stepName = 'basic-info';
                $routeStep = 1;
                $focusTarget = match ($missingKey) {
                    'title' => 'title',
                    'short_description' => 'short_description',
                    'description' => 'description',
                    'category' => 'category',
                    'will_learn', 'outcomes' => 'will_learn',
                    'requirements' => 'requirements',
                    default => 'title'
                };
            } elseif ($missingKey === 'price') {
                $stepName = 'pricing';
                $routeStep = 2;
                $focusTarget = 'price';
            } elseif (in_array($missingKey, ['thumbnail', 'intro_video'], true)) {
                $stepName = 'media';
                $routeStep = 3;
                $focusTarget = match ($missingKey) {
                    'thumbnail' => 'thumbnail',
                    'intro_video' => 'intro_video',
                    default => 'thumbnail'
                };
            } elseif (in_array($missingKey, ['section', 'published_section', 'lesson', 'published_lesson', 'lesson_media'], true)) {
                $stepName = 'curriculum';
                $routeStep = 4;
                if ($missingKey === 'published_section') {
                    $draftSec = $sections->where('status', '!=', 'published')->first();
                    $sectionId = $draftSec?->id ?? $sections->first()?->id;
                    $focusTarget = 'published_section';
                } elseif (in_array($missingKey, ['lesson', 'published_lesson', 'lesson_media'], true)) {
                    $sectionId = $sections->first()?->id;
                    $focusTarget = 'add-lesson';
                } else {
                    $focusTarget = 'add-section';
                }
            }

            $queryParams = "step={$stepName}&focus={$focusTarget}";
            if ($sectionId) {
                $queryParams .= "&section_id={$sectionId}";
            }

            $nextStep = [
                'key' => $missingKey,
                'label' => 'Thêm ' . mb_strtolower($firstMissing['label'], 'UTF-8'),
                'step' => $stepName,
                'route_step' => $routeStep,
                'section_id' => $sectionId,
                'lesson_id' => $lessonId,
                'focus' => $focusTarget,
                'anchor' => "focus-{$focusTarget}",
                'route' => "/instructor/courses/{$courseId}/edit?{$queryParams}",
            ];
        }

        $actionLabel = 'Tiếp tục cập nhật';
        if ($course->status === 'rejected') {
            $actionLabel = 'Xem lý do';
        } elseif ($completionPercent === 100 && $course->status === 'draft') {
            $actionLabel = 'Gửi duyệt';
        } elseif ($completionPercent === 0) {
            $actionLabel = 'Bắt đầu cập nhật';
        }

        return [
            'completion_percent' => $completionPercent,
            'completed_items' => $completedItems,
            'total_items' => $totalItems,
            'missing_items' => $missingItems,
            'next_step' => $nextStep,
            'action_label' => $actionLabel,
            'passed' => $completedItems === $totalItems,
        ];
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

        $missingItems = [];
        $warnings = [];
        $checks = [];

        $this->checkCourseInfo($course, $checks, $missingItems, $warnings, $strict);
        $this->checkCategories($categories, $checks, $missingItems, $warnings);
        $this->checkSections($sections, $checks, $missingItems, $warnings);
        $this->checkLessons($lessons, $assetCount, $checks, $missingItems, $warnings, $strict);

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
            'Thông tin cơ bản của khóa học',
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

            if (! in_array($lessonType, ['video', 'text', 'article', 'document'], true)
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
            'Bài học và nội dung bài học',
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

