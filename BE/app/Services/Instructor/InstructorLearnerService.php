<?php
namespace App\Services\Instructor;
use App\Exceptions\BusinessException;
use App\Http\Resources\Instructor\LessonProgressResource;
use App\Models\User;
use App\Repositories\Instructor\InstructorLearnerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
final class InstructorLearnerService
{
    public function __construct(
        private readonly InstructorLearnerRepository $instructorLearnerRepository,
    ) {
    }
    public function getSummary(User $instructor, array $filters): array
    {
        $courseId = !empty($filters['course_id'])
            ? (int) $filters['course_id']
            : null;
        if ($courseId !== null) {
            $this->ensureCourseBelongsToInstructor($courseId, (int) $instructor->id);
        }
        return $this->instructorLearnerRepository->getSummary(
            (int) $instructor->id,
            $courseId,
        );
    }
    public function paginateLearners(User $instructor, array $filters): LengthAwarePaginator
    {
        if (!empty($filters['course_id'])) {
            $this->ensureCourseBelongsToInstructor(
                (int) $filters['course_id'],
                (int) $instructor->id,
            );
        }
        return $this->instructorLearnerRepository->paginateLearners(
            (int) $instructor->id,
            $filters,
        );
    }
    public function getEnrollmentDetail(
        User $instructor,
        int $enrollmentId,
        bool $includeLessonProgress = true,
    ): array {
        $enrollment = $this->getOwnedEnrollmentOrFail(
            $enrollmentId,
            (int) $instructor->id,
        );
        return [
            'enrollment' => $enrollment,
            'lesson_progress' => $includeLessonProgress
                ? $this->formatLessonProgressFlat($enrollment)
                : [],
        ];
    }
    public function getLessonProgress(
        User $instructor,
        int $enrollmentId,
        bool $groupBySection = true,
    ): array {
        $enrollment = $this->getOwnedEnrollmentOrFail(
            $enrollmentId,
            (int) $instructor->id,
        );
        return [
            'enrollment_id' => (int) $enrollment->enrollment_id,
            'learner' => [
                'id' => (int) $enrollment->learner_id,
                'full_name' => $enrollment->learner_full_name,
                'email' => $enrollment->learner_email,
            ],
            'course' => [
                'id' => (int) $enrollment->course_id,
                'title' => $enrollment->course_title,
            ],
            'sections' => $groupBySection
                ? $this->formatLessonProgressGrouped($enrollment)
                : $this->formatLessonProgressFlat($enrollment),
        ];
    }
    public function getCourseOptions(User $instructor): array
    {
        return $this->instructorLearnerRepository
            ->getCourseOptions((int) $instructor->id)
            ->map(function (object $course): array {
                return [
                    'id' => (int) $course->id,
                    'title' => $course->title,
                    'status' => $course->status,
                    'status_label' => $this->courseStatusLabel($course->status),
                ];
            })
            ->values()
            ->all();
    }
    private function ensureCourseBelongsToInstructor(
        int $courseId,
        int $instructorId,
    ): void {
        if (!$this->instructorLearnerRepository->courseBelongsToInstructor($courseId, $instructorId)) {
            throw new BusinessException(
                'Không tìm thấy khóa học hoặc bạn không có quyền xem.',
                404,
            );
        }
    }
    private function getOwnedEnrollmentOrFail(
        int $enrollmentId,
        int $instructorId,
    ): object {
        $enrollment = $this->instructorLearnerRepository->findEnrollmentForInstructor(
            $enrollmentId,
            $instructorId,
        );
        if (!$enrollment) {
            throw new BusinessException(
                'Không tìm thấy lượt ghi danh hoặc bạn không có quyền xem.',
                404,
            );
        }
        return $enrollment;
    }
    private function formatLessonProgressFlat(object $enrollment): array
    {
        return LessonProgressResource::collection(
            $this->instructorLearnerRepository->getLessonProgressRows($enrollment),
        )->resolve(request());
    }
    private function formatLessonProgressGrouped(object $enrollment): array
    {
        $rows = $this->instructorLearnerRepository->getLessonProgressRows($enrollment);
        return $rows
            ->groupBy('section_id')
            ->map(function (Collection $sectionRows): array {
                $firstRow = $sectionRows->first();
                return [
                    'section_id' => (int) $firstRow->section_id,
                    'title' => $firstRow->section_title,
                    'sort_order' => (int) $firstRow->section_sort_order,
                    'lessons' => LessonProgressResource::collection($sectionRows)->resolve(request()),
                ];
            })
            ->values()
            ->all();
    }
    private function courseStatusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'Bản nháp',
            'pending_review' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Bị từ chối',
            'published' => 'Đang công khai',
            'hidden' => 'Đang ẩn',
            default => 'Không xác định',
        };
    }
}