<?php
namespace App\Repositories\Instructor;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
final class InstructorLessonRepository
{
    public function paginateOwnedLessons(User $instructor, array $filters): LengthAwarePaginator
    {
        $query = Lesson::query()
            ->with(['course', 'section', 'assets'])
            ->whereHas('course', function ($query) use ($instructor): void {
                $query->where('instructor_id', $instructor->id);
            });
        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }
        if (!empty($filters['course_section_id'])) {
            $query->where('course_section_id', (int) $filters['course_section_id']);
        }
        if (!empty($filters['lesson_type'])) {
            $query->where('lesson_type', $filters['lesson_type']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }
        $sortBy = $filters['sort_by'] ?? 'sort_order';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $perPage = (int) ($filters['per_page'] ?? 10);
        return $query
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('id')
            ->paginate($perPage);
    }
    public function findByIdWithCourse(int $lessonId): ?Lesson
    {
        return Lesson::query()
            ->with(['course', 'section', 'assets'])
            ->whereKey($lessonId)
            ->first();
    }
    public function findCourseById(int $courseId): ?Course
    {
        return Course::query()
            ->whereKey($courseId)
            ->first();
    }
    public function findSectionById(int $sectionId): ?CourseSection
    {
        return CourseSection::query()
            ->with('course')
            ->whereKey($sectionId)
            ->first();
    }
    public function create(array $lessonData): Lesson
    {
        return Lesson::query()->create($lessonData);
    }
    public function update(Lesson $lesson, array $lessonData): Lesson
    {
        $lesson->update($lessonData);
        return $lesson->refresh();
    }
    public function delete(Lesson $lesson): void
    {
        $lesson->delete();
    }
    public function updateVideo(Lesson $lesson, string $videoUrl, ?int $videoDurationSeconds): Lesson
    {
        $lesson->update([
            'lesson_type' => 'video',
            'video_url' => $videoUrl,
            'video_duration_seconds' => $videoDurationSeconds ?? 0,
        ]);
        return $lesson->refresh();
    }
    public function getNextSortOrder(int $sectionId): int
    {
        $maxSortOrder = Lesson::query()
            ->where('course_section_id', $sectionId)
            ->max('sort_order');
        return ((int) $maxSortOrder) + 1;
    }
    public function slugExistsInCourse(int $courseId, string $slug, ?int $ignoreLessonId = null): bool
    {
        return Lesson::query()
            ->where('course_id', $courseId)
            ->where('slug', $slug)
            ->when($ignoreLessonId !== null, function ($query) use ($ignoreLessonId): void {
                $query->whereKeyNot($ignoreLessonId);
            })
            ->exists();
    }
}
