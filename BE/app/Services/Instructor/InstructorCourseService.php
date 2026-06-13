<?php
namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonAsset;
use App\Models\User;
use App\Models\Category;
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
        private readonly FileUpload $fileUpload,
    ) {}
    public function createCourse(User $instructor, array $validatedData): Course
    {
        return DB::transaction(function () use (
            $instructor,
            $validatedData,
        ): Course {
            $categoryIds = $validatedData["category_ids"] ?? [];
            unset($validatedData["category_ids"]);
            $courseData = array_merge($validatedData, [
                "instructor_id" => $instructor->id,
                "status" => "draft",
                "is_featured" => false,
                "total_duration_seconds" => 0,
                "published_at" => null,
                "admin_reject_reason" => null,
                "language" => $validatedData["language"] ?? "vi",
                "level" => $validatedData["level"] ?? "beginner",
            ]);
            $course = $this->instructorCourseRepository->create($courseData);
            if (!empty($categoryIds)) {
                $this->instructorCourseRepository->syncCategories(
                    $course,
                    $categoryIds,
                );
            }
            return $this->instructorCourseRepository->findWithCategories(
                $course->id,
            );
        });
    }
    public function paginateLessons(
        User $instructor,
        array $filters,
    ): LengthAwarePaginator {
        if (!empty($filters["course_id"])) {
            $this->assertCourseOwnedByInstructor(
                (int) $filters["course_id"],
                $instructor,
            );
        }
        if (!empty($filters["course_section_id"])) {
            $section = $this->findSectionOrFail(
                (int) $filters["course_section_id"],
            );
            $this->assertCourseOwnedByInstructor(
                (int) $section->course_id,
                $instructor,
            );
        }
        return $this->instructorLessonRepository->paginateOwnedLessons(
            $instructor,
            $filters,
        );
    }
    public function createLesson(User $instructor, array $validatedData): Lesson
    {
        return DB::transaction(function () use (
            $instructor,
            $validatedData,
        ): Lesson {
            $course = $this->assertCourseOwnedByInstructor(
                (int) $validatedData["course_id"],
                $instructor,
            );
            $section = $this->findSectionOrFail(
                (int) $validatedData["course_section_id"],
            );
            $this->assertSectionBelongsToCourse($section, $course);
            $lessonType = $validatedData["lesson_type"];
            $lessonData = [
                "course_id" => $course->id,
                "course_section_id" => $section->id,
                "title" => $validatedData["title"],
                "slug" => $this->makeUniqueLessonSlug(
                    $course->id,
                    $validatedData["title"],
                ),
                "lesson_type" => $lessonType,
                "content" => $validatedData["content"] ?? null,
                "video_url" => $validatedData["video_url"] ?? null,
                "video_duration_seconds" =>
                    $validatedData["video_duration_seconds"] ?? 0,
                "is_preview" => $validatedData["is_preview"] ?? false,
                "status" => $validatedData["status"] ?? "draft",
                "sort_order" =>
                    $validatedData["sort_order"] ??
                    $this->instructorLessonRepository->getNextSortOrder(
                        $section->id,
                    ),
            ];
            if ($lessonType === "text") {
                $lessonData["video_url"] = null;
                $lessonData["video_duration_seconds"] = 0;
            }
            return $this->instructorLessonRepository
                ->create($lessonData)
                ->load(["course", "section", "assets"]);
        });
    }
    public function getLesson(User $instructor, int $lessonId): Lesson
    {
        return $this->findOwnedLessonOrFail($instructor, $lessonId);
    }
    public function updateLesson(
        User $instructor,
        int $lessonId,
        array $validatedData,
    ): Lesson {
        return DB::transaction(function () use (
            $instructor,
            $lessonId,
            $validatedData,
        ): Lesson {
            $lesson = $this->findOwnedLessonOrFail($instructor, $lessonId);
            $targetCourseId =
                (int) ($validatedData["course_id"] ?? $lesson->course_id);
            $targetSectionId =
                (int) ($validatedData["course_section_id"] ??
                    $lesson->course_section_id);
            $course = $this->assertCourseOwnedByInstructor(
                $targetCourseId,
                $instructor,
            );
            $section = $this->findSectionOrFail($targetSectionId);
            $this->assertSectionBelongsToCourse($section, $course);
            $lessonType = $validatedData["lesson_type"] ?? $lesson->lesson_type;
            $lessonData = [
                "course_id" => $course->id,
                "course_section_id" => $section->id,
                "lesson_type" => $lessonType,
            ];
            foreach (
                [
                    "title",
                    "content",
                    "video_url",
                    "video_duration_seconds",
                    "is_preview",
                    "status",
                    "sort_order",
                ]
                as $field
            ) {
                if (array_key_exists($field, $validatedData)) {
                    $lessonData[$field] = $validatedData[$field];
                }
            }
            if (array_key_exists("title", $validatedData)) {
                $lessonData["slug"] = $this->makeUniqueLessonSlug(
                    $course->id,
                    $validatedData["title"],
                    $lesson->id,
                );
            }
            if ($lessonType === "text") {
                $lessonData["video_url"] = null;
                $lessonData["video_duration_seconds"] = 0;
            }
            return $this->instructorLessonRepository
                ->update($lesson, $lessonData)
                ->load(["course", "section", "assets"]);
        });
    }
    public function deleteLesson(User $instructor, int $lessonId): void
    {
        DB::transaction(function () use ($instructor, $lessonId): void {
            $lesson = $this->findOwnedLessonOrFail($instructor, $lessonId);
            $this->instructorLessonRepository->delete($lesson);
        });
    }
    public function toggleLessonPreview(
        User $instructor,
        int $lessonId,
        bool $isPreview,
    ): Lesson {
        return DB::transaction(function () use (
            $instructor,
            $lessonId,
            $isPreview,
        ): Lesson {
            $lesson = $this->findOwnedLessonOrFail($instructor, $lessonId);
            if ($isPreview && $lesson->status === "hidden") {
                throw new BusinessException(
                    "Bài học đang ẩn không thể bật preview miễn phí.",
                    400,
                );
            }
            return $this->instructorLessonRepository
                ->update($lesson, [
                    "is_preview" => $isPreview,
                ])
                ->load(["course", "section", "assets"]);
        });
    }
    public function uploadLessonVideo(
        User $instructor,
        int $lessonId,
        array $validatedData,
        UploadedFile $video,
    ): Lesson {
        return DB::transaction(function () use (
            $instructor,
            $lessonId,
            $validatedData,
            $video,
        ): Lesson {
            $lesson = $this->findOwnedLessonOrFail($instructor, $lessonId);
            $videoUrl = $this->fileUpload->uploadLessonVideo(
                $video,
                $lesson->id,
            );
            return $this->instructorLessonRepository
                ->updateVideo(
                    $lesson,
                    $videoUrl,
                    $validatedData["video_duration_seconds"] ?? null,
                )
                ->load(["course", "section", "assets"]);
        });
    }
    public function uploadLessonAsset(
        User $instructor,
        int $lessonId,
        array $validatedData,
        UploadedFile $file,
    ): LessonAsset {
        return DB::transaction(function () use (
            $instructor,
            $lessonId,
            $validatedData,
            $file,
        ): LessonAsset {
            $lesson = $this->findOwnedLessonOrFail($instructor, $lessonId);
            $uploadedFile = $this->fileUpload->uploadLessonAsset(
                $file,
                $lesson->id,
            );
            $title =
                $validatedData["title"] ??
                pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            return LessonAsset::create([
                "lesson_id" => $lesson->id,
                "title" => $title,
                "file_url" => $uploadedFile["file_url"],
                "file_name" => $uploadedFile["file_name"],
                "file_type" => $uploadedFile["file_type"],
                "file_size" => $uploadedFile["file_size"],
                "note" => $validatedData["note"] ?? null,
            ]);
        });
    }

    public function submitForReview(User $instructor, int $courseId): Course
    {
        return DB::transaction(function () use (
            $instructor,
            $courseId,
        ): Course {
            $course = $this->instructorCourseRepository->findByIdWithReviewRelations(
                $courseId,
            );
            if (!$course) {
                throw new NotFoundHttpException("Không tìm thấy dữ liệu.");
            }
            if ((int) $course->instructor_id !== (int) $instructor->id) {
                throw new BusinessException(
                    "Bạn không có quyền thao tác tài nguyên này.",
                    403,
                );
            }
            if (!$this->courseCanBeSubmitted($course)) {
                throw new BusinessException(
                    "Khóa học chưa đủ điều kiện gửi duyệt.",
                    400,
                );
            }
            return $this->instructorCourseRepository->markAsPendingReview(
                $course,
            );
        });
    }
    public function getRejectedReviewNotes(
        User $instructor,
        int $courseId,
    ): Course {
        $course = $this->instructorCourseRepository->findByIdWithReviewRelations(
            $courseId,
        );
        if (!$course) {
            throw new NotFoundHttpException("Không tìm thấy dữ liệu.");
        }
        if ((int) $course->instructor_id !== (int) $instructor->id) {
            throw new BusinessException(
                "Bạn không có quyền thao tác tài nguyên này.",
                403,
            );
        }
        if ($course->status !== "rejected") {
            throw new NotFoundHttpException("Không tìm thấy dữ liệu.");
        }
        return $course;
    }

    private function courseCanBeSubmitted(Course $course): bool
    {
        if (!in_array($course->status, ["draft", "rejected"], true)) {
            return false;
        }
        foreach (
            [
                "title",
                "slug",
                "short_description",
                "description",
                "level",
                "language",
                "requirements",
                "outcomes",
            ]
            as $requiredField
        ) {
            if (trim((string) $course->{$requiredField}) === "") {
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
            fn(CourseSection $section): int => $section->lessons->count(),
        );
        return $lessonCount > 0;
    }

    private function findOwnedLessonOrFail(
        User $instructor,
        int $lessonId,
    ): Lesson {
        $lesson = $this->instructorLessonRepository->findByIdWithCourse(
            $lessonId,
        );
        if (!$lesson) {
            throw new NotFoundHttpException("Không tìm thấy dữ liệu.");
        }
        if (
            !$lesson->course ||
            (int) $lesson->course->instructor_id !== (int) $instructor->id
        ) {
            throw new AccessDeniedHttpException(
                "Bạn không có quyền thao tác tài nguyên này.",
            );
        }
        return $lesson->load(["course", "section", "assets"]);
    }
    private function assertCourseOwnedByInstructor(
        int $courseId,
        User $instructor,
    ): Course {
        $course = $this->instructorLessonRepository->findCourseById($courseId);
        if (!$course) {
            throw new NotFoundHttpException("Không tìm thấy dữ liệu.");
        }
        if ((int) $course->instructor_id !== (int) $instructor->id) {
            throw new AccessDeniedHttpException(
                "Bạn không có quyền thao tác tài nguyên này.",
            );
        }
        return $course;
    }
    private function findSectionOrFail(int $sectionId): CourseSection
    {
        $section = $this->instructorLessonRepository->findSectionById(
            $sectionId,
        );
        if (!$section) {
            throw new NotFoundHttpException("Không tìm thấy dữ liệu.");
        }
        return $section;
    }
    private function assertSectionBelongsToCourse(
        CourseSection $section,
        Course $course,
    ): void {
        if ((int) $section->course_id !== (int) $course->id) {
            throw new HttpException(422, "Tham số không hợp lệ.");
        }
    }
    private function makeUniqueLessonSlug(
        int $courseId,
        string $title,
        ?int $ignoreLessonId = null,
    ): string {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;
        while (
            $this->instructorLessonRepository->slugExistsInCourse(
                $courseId,
                $slug,
                $ignoreLessonId,
            )
        ) {
            $counter++;
            $slug = $baseSlug . "-" . $counter;
        }
        return $slug;
    }

    public function updateCourse(
        int $courseId,
        int $instructorId,
        array $data,
    ): Course {
        $course = Course::query()->where("id", $courseId)->first();

        if (!$course) {
            throw new BusinessException("Không tìm thấy dữ liệu.", 404);
        }

        if ((int) $course->instructor_id !== (int) $instructorId) {
            throw new BusinessException(
                "Bạn không có quyền thao tác tài nguyên này.",
                403,
            );
        }

        $this->validateSalePrice($course, $data);

        $categoryIds = null;

        if (array_key_exists("category_ids", $data)) {
            $categoryIds = $data["category_ids"];
            unset($data["category_ids"]);

            $this->validateCategoryIds($categoryIds);
        }

        $this->removeForbiddenFields($data);

        return DB::transaction(function () use (
            $course,
            $data,
            $categoryIds,
        ): Course {
            $course->update($data);

            if ($categoryIds !== null) {
                $course->categories()->sync($categoryIds);
            }

            return $course->refresh()->load("categories");
        });
    }

    private function validateSalePrice(Course $course, array $data): void
    {
        $effectivePrice = array_key_exists("price", $data)
            ? $data["price"]
            : $course->price;

        $effectiveSalePrice = array_key_exists("sale_price", $data)
            ? $data["sale_price"]
            : $course->sale_price;

        if (
            $effectiveSalePrice !== null &&
            (float) $effectiveSalePrice > (float) $effectivePrice
        ) {
            throw new BusinessException(
                "Giá khuyến mãi không được lớn hơn giá gốc.",
                422,
            );
        }
    }

    private function validateCategoryIds(array $categoryIds): void
    {
        if ($categoryIds === []) {
            return;
        }

        $validCategoryCount = Category::query()
            ->whereIn("id", $categoryIds)
            ->where("status", "active")
            ->whereNull("deleted_at")
            ->count();

        if ($validCategoryCount !== count(array_unique($categoryIds))) {
            throw new BusinessException("Danh mục không hợp lệ.", 422);
        }
    }

    private function removeForbiddenFields(array &$data): void
    {
        unset(
            $data["id"],
            $data["instructor_id"],
            $data["is_featured"],
            $data["total_duration_seconds"],
            $data["published_at"],
            $data["admin_reject_reason"],
            $data["deleted_at"],
            $data["created_at"],
            $data["updated_at"],
        );
    }

    public function getSections(
        array $queryParams,
        int $instructorId,
    ): LengthAwarePaginator {
        $perPage = min((int) ($queryParams["per_page"] ?? 10), 100);

        $query = CourseSection::query()
            ->with("course:id,instructor_id,title,slug,status")
            ->whereHas("course", function ($builder) use ($instructorId): void {
                $builder->where("instructor_id", $instructorId);
            });

        if (!empty($queryParams["course_id"])) {
            $this->ensureCourseBelongsToInstructor(
                (int) $queryParams["course_id"],
                $instructorId,
            );

            $query->where("course_id", (int) $queryParams["course_id"]);
        }

        if (!empty($queryParams["search"])) {
            $search = trim((string) $queryParams["search"]);

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where("title", "like", "%{$search}%")
                    ->orWhere("description", "like", "%{$search}%");
            });
        }

        if (!empty($queryParams["status"])) {
            $query->where("status", $queryParams["status"]);
        }

        $sortBy = $queryParams["sort_by"] ?? "sort_order";
        $sortDirection = $queryParams["sort_direction"] ?? "asc";

        return $query
            ->orderBy($sortBy, $sortDirection)
            ->orderBy("id")
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getSection(int $sectionId, int $instructorId): CourseSection
    {
        return $this->getOwnedSection($sectionId, $instructorId);
    }

    public function createSection(array $data, int $instructorId): CourseSection
    {
        $course = $this->ensureCourseBelongsToInstructor(
            (int) $data["course_id"],
            $instructorId,
        );

        $data["status"] ??= "draft";

        if (
            !array_key_exists("sort_order", $data) ||
            $data["sort_order"] === null
        ) {
            $data["sort_order"] = $this->getNextSectionSortOrder(
                (int) $course->id,
            );
        }

        return DB::transaction(function () use ($data): CourseSection {
            return CourseSection::create($data)->load(
                "course:id,instructor_id,title,slug,status",
            );
        });
    }

    public function updateSection(
        int $sectionId,
        array $data,
        int $instructorId,
    ): CourseSection {
        $section = $this->getOwnedSection($sectionId, $instructorId);

        $this->removeForbiddenSectionFields($data);

        return DB::transaction(function () use (
            $section,
            $data,
        ): CourseSection {
            $section->update($data);

            return $section
                ->refresh()
                ->load("course:id,instructor_id,title,slug,status");
        });
    }

    public function deleteSection(int $sectionId, int $instructorId): void
    {
        $section = $this->getOwnedSection($sectionId, $instructorId);

        DB::transaction(function () use ($section): void {
            $section->delete();
        });
    }

    private function ensureCourseBelongsToInstructor(
        int $courseId,
        int $instructorId,
    ): Course {
        $course = Course::query()->find($courseId);

        if (!$course) {
            throw new BusinessException("Không tìm thấy dữ liệu.", 404);
        }

        if ((int) $course->instructor_id !== (int) $instructorId) {
            throw new BusinessException(
                "Bạn không có quyền thao tác tài nguyên này.",
                403,
            );
        }

        return $course;
    }

    private function getOwnedSection(
        int $sectionId,
        int $instructorId,
    ): CourseSection {
        $section = CourseSection::query()
            ->with("course:id,instructor_id,title,slug,status")
            ->find($sectionId);

        if (!$section) {
            throw new BusinessException("Không tìm thấy dữ liệu.", 404);
        }

        if (!$section->course) {
            throw new BusinessException("Không tìm thấy dữ liệu.", 404);
        }

        if ((int) $section->course->instructor_id !== (int) $instructorId) {
            throw new BusinessException(
                "Bạn không có quyền thao tác tài nguyên này.",
                403,
            );
        }

        return $section;
    }

    private function getNextSectionSortOrder(int $courseId): int
    {
        $maxSortOrder = CourseSection::query()
            ->where("course_id", $courseId)
            ->max("sort_order");

        return ((int) $maxSortOrder) + 1;
    }

    private function removeForbiddenSectionFields(array &$data): void
    {
        unset(
            $data["id"],
            $data["course_id"],
            $data["deleted_at"],
            $data["created_at"],
            $data["updated_at"],
        );
    }

    public function getInstructorProfile(int $userId): \App\Models\InstructorProfile
    {
        $profile = \App\Models\InstructorProfile::query()
            ->with("user")
            ->where("user_id", $userId)
            ->first();

        if (!$profile) {
            throw new BusinessException("Không tìm thấy dữ liệu.", 404);
        }

        return $profile;
    }

    public function updateInstructorProfile(int $userId, array $data): \App\Models\InstructorProfile
    {
        $allowedData = [];
        $fields = ["bio", "expertise", "experience_years", "level"];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $allowedData[$field] = $data[$field];
            }
        }

        $profile = \App\Models\InstructorProfile::updateOrCreate(
            ["user_id" => $userId],
            $allowedData
        );

        return $profile->refresh()->load("user");
    }
}
