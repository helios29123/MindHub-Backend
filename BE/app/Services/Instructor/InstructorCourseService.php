<?php
namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Instructor\InstructorLessonRepository;
use App\Support\FileUpload;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
final class InstructorCourseService
{
    public function __construct(
        private readonly InstructorCourseRepository $instructorCourseRepository,
        private readonly InstructorLessonRepository $instructorLessonRepository,
        private readonly FileUpload $fileUpload
    ) {
    }
    public function createCourse(User $instructor, array $validatedData): Course
    {
        return DB::transaction(function () use ($instructor, $validatedData): Course {
            $categoryIds = $validatedData['category_ids'] ?? [];
            unset($validatedData['category_ids']);
            $courseData = array_merge($validatedData, [
                'instructor_id' => $instructor->id,
                'status' => 'draft',
                'is_featured' => false,
                'total_duration_seconds' => 0,
                'published_at' => null,
                'admin_reject_reason' => null,
                'language' => $validatedData['language'] ?? 'vi',
                'level' => $validatedData['level'] ?? 'beginner',
            ]);
            $course = $this->instructorCourseRepository->create($courseData);
            if (!empty($categoryIds)) {
                $this->instructorCourseRepository->syncCategories($course, $categoryIds);
            }
            return $this->instructorCourseRepository->findWithCategories($course->id);
        });
    }
    public function paginateLessons(User $instructor, array $filters): LengthAwarePaginator
    {
        if (!empty($filters['course_id'])) {
            $this->assertCourseOwnedByInstructor((int) $filters['course_id'], $instructor);
        }
        if (!empty($filters['course_section_id'])) {
            $section = $this->findSectionOrFail((int) $filters['course_section_id']);
            $this->assertCourseOwnedByInstructor((int) $section->course_id, $instructor);
        }
        return $this->instructorLessonRepository->paginateOwnedLessons($instructor, $filters);
    }
    public function createLesson(User $instructor, array $validatedData): Lesson
    {
        return DB::transaction(function () use ($instructor, $validatedData): Lesson {
            $course = $this->assertCourseOwnedByInstructor((int) $validatedData['course_id'], $instructor);
            $section = $this->findSectionOrFail((int) $validatedData['course_section_id']);
            $this->assertSectionBelongsToCourse($section, $course);
            $lessonType = $validatedData['lesson_type'];
            $lessonData = [
                'course_id' => $course->id,
                'course_section_id' => $section->id,
                'title' => $validatedData['title'],
                'slug' => $this->makeUniqueLessonSlug($course->id, $validatedData['title']),
                'lesson_type' => $lessonType,
                'content' => $validatedData['content'] ?? null,
                'video_url' => $validatedData['video_url'] ?? null,
                'video_duration_seconds' => $validatedData['video_duration_seconds'] ?? 0,
                'is_preview' => $validatedData['is_preview'] ?? false,
                'status' => $validatedData['status'] ?? 'draft',
                'sort_order' => $validatedData['sort_order']
                    ?? $this->instructorLessonRepository->getNextSortOrder($section->id),
            ];
            if ($lessonType === 'text') {
                $lessonData['video_url'] = null;
                $lessonData['video_duration_seconds'] = 0;
            }
            return $this->instructorLessonRepository
                ->create($lessonData)
                ->load(['course', 'section', 'assets']);
        });
    }
    public function getLesson(User $instructor, int $lessonId): Lesson
    {
        return $this->findOwnedLessonOrFail($instructor, $lessonId);
    }
    public function updateLesson(User $instructor, int $lessonId, array $validatedData): Lesson
    {
        return DB::transaction(function () use ($instructor, $lessonId, $validatedData): Lesson {
            $lesson = $this->findOwnedLessonOrFail($instructor, $lessonId);
            $targetCourseId = (int) ($validatedData['course_id'] ?? $lesson->course_id);
            $targetSectionId = (int) ($validatedData['course_section_id'] ?? $lesson->course_section_id);
            $course = $this->assertCourseOwnedByInstructor($targetCourseId, $instructor);
            $section = $this->findSectionOrFail($targetSectionId);
            $this->assertSectionBelongsToCourse($section, $course);
            $lessonType = $validatedData['lesson_type'] ?? $lesson->lesson_type;
            $lessonData = [
                'course_id' => $course->id,
                'course_section_id' => $section->id,
                'lesson_type' => $lessonType,
            ];
            foreach ([
                'title',
                'content',
                'video_url',
                'video_duration_seconds',
                'is_preview',
                'status',
                'sort_order',
            ] as $field) {
                if (array_key_exists($field, $validatedData)) {
                    $lessonData[$field] = $validatedData[$field];
                }
            }
            if (array_key_exists('title', $validatedData)) {
                $lessonData['slug'] = $this->makeUniqueLessonSlug(
                    $course->id,
                    $validatedData['title'],
                    $lesson->id
                );
            }
            if ($lessonType === 'text') {
                $lessonData['video_url'] = null;
                $lessonData['video_duration_seconds'] = 0;
            }
            return $this->instructorLessonRepository
                ->update($lesson, $lessonData)
                ->load(['course', 'section', 'assets']);
        });
    }
    public function deleteLesson(User $instructor, int $lessonId): void
    {
        DB::transaction(function () use ($instructor, $lessonId): void {
            $lesson = $this->findOwnedLessonOrFail($instructor, $lessonId);
            $this->instructorLessonRepository->delete($lesson);
        });
    }
    public function uploadLessonVideo(User $instructor, int $lessonId, array $validatedData, UploadedFile $video): Lesson
    {
        return DB::transaction(function () use ($instructor, $lessonId, $validatedData, $video): Lesson {
            $lesson = $this->findOwnedLessonOrFail($instructor, $lessonId);
            $videoUrl = $this->fileUpload->uploadLessonVideo($video, $lesson->id);
            return $this->instructorLessonRepository
                ->updateVideo(
                    $lesson,
                    $videoUrl,
                    $validatedData['video_duration_seconds'] ?? null
                )
                ->load(['course', 'section', 'assets']);
        });
    }
    public function submitForReview(User $instructor, int $courseId): Course
    {
        return DB::transaction(function () use ($instructor, $courseId): Course {
            $course = $this->instructorCourseRepository->findByIdWithReviewRelations($courseId);
            if (! $course) {
                throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
            }
            if ((int) $course->instructor_id !== (int) $instructor->id) {
                throw new BusinessException('Bạn không có quyền thao tác tài nguyên này.', 403);
            }
            if (! $this->courseCanBeSubmitted($course)) {
                throw new BusinessException('Khóa học chưa đủ điều kiện gửi duyệt.', 400);
            }
            return $this->instructorCourseRepository->markAsPendingReview($course);
        });
    }
    public function getRejectedReviewNotes(User $instructor, int $courseId): Course
    {
        $course = $this->instructorCourseRepository->findByIdWithReviewRelations($courseId);
        if (! $course) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }
        if ((int) $course->instructor_id !== (int) $instructor->id) {
            throw new BusinessException('Bạn không có quyền thao tác tài nguyên này.', 403);
        }
        if ($course->status !== 'rejected') {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }
        return $course;
    }    private function courseCanBeSubmitted(Course $course): bool
    {
        if (! in_array($course->status, ['draft', 'rejected'], true)) {
            return false;
        }
        foreach ([
            'title',
            'slug',
            'short_description',
            'description',
            'level',
            'language',
            'requirements',
            'outcomes',
        ] as $requiredField) {
            if (trim((string) $course->{$requiredField}) === '') {
                return false;
            }
        }
        if ($course->categories->isEmpty()) {
            return false;
        }
        if ($course->sections->isEmpty()) {
            return false;
        }
        $lessonCount = $course->sections->sum(
            fn (CourseSection $section): int => $section->lessons->count()
        );
        return $lessonCount > 0;
    }    private function findOwnedLessonOrFail(User $instructor, int $lessonId): Lesson
    {
        $lesson = $this->instructorLessonRepository->findByIdWithCourse($lessonId);
        if (!$lesson) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }
        if (!$lesson->course || (int) $lesson->course->instructor_id !== (int) $instructor->id) {
            throw new AccessDeniedHttpException('Bạn không có quyền thao tác tài nguyên này.');
        }
        return $lesson->load(['course', 'section', 'assets']);
    }
    private function assertCourseOwnedByInstructor(int $courseId, User $instructor): Course
    {
        $course = $this->instructorLessonRepository->findCourseById($courseId);
        if (!$course) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }
        if ((int) $course->instructor_id !== (int) $instructor->id) {
            throw new AccessDeniedHttpException('Bạn không có quyền thao tác tài nguyên này.');
        }
        return $course;
    }
    private function findSectionOrFail(int $sectionId): CourseSection
    {
        $section = $this->instructorLessonRepository->findSectionById($sectionId);
        if (!$section) {
            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
        }
        return $section;
    }
    private function assertSectionBelongsToCourse(CourseSection $section, Course $course): void
    {
        if ((int) $section->course_id !== (int) $course->id) {
            throw new HttpException(422, 'Tham số không hợp lệ.');
        }
    }
    private function makeUniqueLessonSlug(int $courseId, string $title, ?int $ignoreLessonId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;
        while ($this->instructorLessonRepository->slugExistsInCourse($courseId, $slug, $ignoreLessonId)) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }
        return $slug;
    }
}
