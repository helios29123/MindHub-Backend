<?php

namespace App\Services\Learning;

use App\Exceptions\BusinessException;
use App\Repositories\Learning\LearningRepository;
use Illuminate\Support\Collection;

class CourseLearningOverviewService
{
    public function __construct(
        private readonly LearningRepository $learningRepository
    ) {
    }

    public function getOverview(int $userId, int $courseId, array $filters = []): array
    {
        $includeSections = (bool) ($filters['include_sections'] ?? true);
        $includeNextLesson = (bool) ($filters['include_next_lesson'] ?? true);

        $course = $this->learningRepository->findCourseForLearningOverview($courseId);

        if (! $course) {
            throw new BusinessException('Khﾃｴng tﾃｬm th蘯･y khﾃｳa h盻皇.', 404);
        }

        $enrollment = $this->learningRepository->findEnrollmentForUserCourse($userId, $courseId);

        if (! $enrollment) {
            throw new BusinessException('B蘯｡n chﾆｰa cﾃｳ quy盻］ truy c蘯ｭp khﾃｳa h盻皇 nﾃy.', 403);
        }

        $lessons = $this->learningRepository->getPublishedLessonsWithProgress($userId, $courseId);

        $currentLesson = $this->resolveCurrentLesson($lessons);
        $nextLesson = $includeNextLesson
            ? $this->resolveNextLesson($lessons, $currentLesson)
            : null;

        $completedLessons = $lessons
            ->filter(fn ($lesson): bool => $this->isLessonCompleted($lesson))
            ->count();

        $inProgressLessons = $lessons
            ->filter(fn ($lesson): bool => $this->isLessonInProgress($lesson))
            ->count();

        return [
            'course_id' => (int) $course->id,
            'course_title' => $course->title,
            'course_thumbnail_url' => $course->thumbnail_url,
            'course_status' => $course->status,

            'enrollment_id' => (int) $enrollment->id,
            'enrollment_status' => $enrollment->status,
            'progress_percent' => (float) $enrollment->progress_percent,
            'completed_at' => $enrollment->completed_at,
            'last_accessed_at' => $this->resolveLastAccessedAt($enrollment, $currentLesson),

            'total_lessons' => $lessons->count(),
            'completed_lessons' => $completedLessons,
            'in_progress_lessons' => $inProgressLessons,

            'current_lesson' => $currentLesson,
            'next_lesson' => $nextLesson,
            'sections' => $includeSections ? $this->buildSections($lessons) : [],
        ];
    }

    private function resolveCurrentLesson(Collection $lessons): ?array
    {
        if ($lessons->isEmpty()) {
            return null;
        }

        $lastAccessedLesson = $lessons
            ->filter(fn ($lesson): bool => $lesson->lesson_last_accessed_at !== null)
            ->sortByDesc(fn ($lesson): string => (string) $lesson->lesson_last_accessed_at)
            ->first();

        if ($lastAccessedLesson) {
            return $this->mapLesson($lastAccessedLesson);
        }

        $firstIncompleteLesson = $lessons
            ->first(fn ($lesson): bool => ! $this->isLessonCompleted($lesson));

        return $this->mapLesson($firstIncompleteLesson ?: $lessons->first());
    }

    private function resolveNextLesson(Collection $lessons, ?array $currentLesson): ?array
    {
        if ($lessons->isEmpty()) {
            return null;
        }

        if ($currentLesson === null) {
            $firstIncompleteLesson = $lessons
                ->first(fn ($lesson): bool => ! $this->isLessonCompleted($lesson));

            return $firstIncompleteLesson ? $this->mapLesson($firstIncompleteLesson) : null;
        }

        $currentLessonId = (int) $currentLesson['lesson_id'];

        $currentIndex = $lessons
            ->values()
            ->search(fn ($lesson): bool => (int) $lesson->lesson_id === $currentLessonId);

        if ($currentIndex === false) {
            $firstIncompleteLesson = $lessons
                ->first(fn ($lesson): bool => ! $this->isLessonCompleted($lesson));

            return $firstIncompleteLesson ? $this->mapLesson($firstIncompleteLesson) : null;
        }

        $orderedLessons = $lessons->values();

        for ($index = $currentIndex + 1; $index < $orderedLessons->count(); $index++) {
            $lesson = $orderedLessons->get($index);

            if (! $this->isLessonCompleted($lesson)) {
                return $this->mapLesson($lesson);
            }
        }

        return null;
    }

    private function buildSections(Collection $lessons): array
    {
        return $lessons
            ->groupBy('section_id')
            ->map(function (Collection $sectionLessons): array {
                $first = $sectionLessons->first();

                return [
                    'section_id' => (int) $first->section_id,
                    'section_title' => $first->section_title,
                    'sort_order' => (int) $first->section_sort_order,
                    'total_lessons' => $sectionLessons->count(),
                    'completed_lessons' => $sectionLessons
                        ->filter(fn ($lesson): bool => $this->isLessonCompleted($lesson))
                        ->count(),
                    'lessons' => $sectionLessons
                        ->map(fn ($lesson): array => $this->mapLesson($lesson))
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    private function mapLesson(object $lesson): array
    {
        return [
            'lesson_id' => (int) $lesson->lesson_id,
            'section_id' => (int) $lesson->section_id,
            'title' => $lesson->lesson_title,
            'lesson_type' => $lesson->lesson_type,
            'status' => $lesson->lesson_status,
            'sort_order' => (int) $lesson->lesson_sort_order,
            'is_preview' => (bool) $lesson->is_preview,
            'progress_status' => $lesson->progress_status,
            'video_current_second' => $lesson->video_current_second !== null
                ? (int) $lesson->video_current_second
                : null,
            'video_duration_seconds' => $lesson->video_duration_seconds !== null
                ? (int) $lesson->video_duration_seconds
                : null,
            'learning_duration_seconds' => $lesson->learning_duration_seconds !== null
                ? (int) $lesson->learning_duration_seconds
                : 0,
            'last_accessed_at' => $lesson->lesson_last_accessed_at,
            'completed_at' => $lesson->lesson_completed_at,
        ];
    }

    private function isLessonCompleted(object $lesson): bool
    {
        return $lesson->progress_status === 'completed'
            || $lesson->lesson_completed_at !== null;
    }

    private function isLessonInProgress(object $lesson): bool
    {
        return ! $this->isLessonCompleted($lesson)
            && in_array($lesson->progress_status, ['started', 'in_progress'], true);
    }

    private function resolveLastAccessedAt(object $enrollment, ?array $currentLesson): ?string
    {
        if ($currentLesson !== null && ! empty($currentLesson['last_accessed_at'])) {
            return (string) $currentLesson['last_accessed_at'];
        }

        return $enrollment->last_accessed_at !== null
            ? (string) $enrollment->last_accessed_at
            : null;
    }
}