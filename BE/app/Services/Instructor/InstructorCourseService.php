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
                    "B繝ｻ繝ｻ・｣・ｰi h騾ｶ・ｻ騾ｧ繝ｻ繝ｻ繝ｻ魄ｪng 髯ゑｽｯ繝ｻ・ｩn kh繝ｻ繝ｻ・ｽ・ｴng th騾ｶ・ｻ郢晢ｽｻb髯ゑｽｯ繝ｻ・ｭt preview mi騾ｶ・ｻ郢晢ｽｻ ph繝ｻ繝ｻ・ｽ・ｭ.",
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
                throw new NotFoundHttpException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.");
            }
            if ((int) $course->instructor_id !== (int) $instructor->id) {
                throw new BusinessException(
                    "B髯ゑｽｯ繝ｻ・｡n kh繝ｻ繝ｻ・ｽ・ｴng c繝ｻ繝ｻ・ｽ・ｳ quy騾ｶ・ｻ繝ｻ・ｽ thao t繝ｻ繝ｻ・ｽ・｡c t繝ｻ繝ｻ・｣・ｰi nguy繝ｻ繝ｻ・ｽ・ｪn n繝ｻ繝ｻ・｣・ｰy.",
                    403,
                );
            }
            if (!$this->courseCanBeSubmitted($course)) {
                throw new BusinessException(
                    "Kh繝ｻ繝ｻ・ｽ・ｳa h騾ｶ・ｻ騾ｧ繝ｻch繝ｻ繝ｻ・ｽ・ｰa 繝ｻ繝ｻ・ｻ蟷｢・ｽ・ｻ繝ｻ・ｧ 繝ｻ繝ｻ・ｨ・ｴ騾ｶ・ｻ邵ｲ繝ｻki騾ｶ・ｻ郢ｻ・ｻ g騾ｶ・ｻ繝ｻ・ｭi duy騾ｶ・ｻ郢ｽ繝ｻ",
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
            throw new NotFoundHttpException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.");
        }
        if ((int) $course->instructor_id !== (int) $instructor->id) {
            throw new BusinessException(
                "B髯ゑｽｯ繝ｻ・｡n kh繝ｻ繝ｻ・ｽ・ｴng c繝ｻ繝ｻ・ｽ・ｳ quy騾ｶ・ｻ繝ｻ・ｽ thao t繝ｻ繝ｻ・ｽ・｡c t繝ｻ繝ｻ・｣・ｰi nguy繝ｻ繝ｻ・ｽ・ｪn n繝ｻ繝ｻ・｣・ｰy.",
                403,
            );
        }
        if ($course->status !== "rejected") {
            throw new NotFoundHttpException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.");
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
            throw new NotFoundHttpException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.");
        }
        if (
            !$lesson->course ||
            (int) $lesson->course->instructor_id !== (int) $instructor->id
        ) {
            throw new AccessDeniedHttpException(
                "B髯ゑｽｯ繝ｻ・｡n kh繝ｻ繝ｻ・ｽ・ｴng c繝ｻ繝ｻ・ｽ・ｳ quy騾ｶ・ｻ繝ｻ・ｽ thao t繝ｻ繝ｻ・ｽ・｡c t繝ｻ繝ｻ・｣・ｰi nguy繝ｻ繝ｻ・ｽ・ｪn n繝ｻ繝ｻ・｣・ｰy.",
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
            throw new NotFoundHttpException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.");
        }
        if ((int) $course->instructor_id !== (int) $instructor->id) {
            throw new AccessDeniedHttpException(
                "B髯ゑｽｯ繝ｻ・｡n kh繝ｻ繝ｻ・ｽ・ｴng c繝ｻ繝ｻ・ｽ・ｳ quy騾ｶ・ｻ繝ｻ・ｽ thao t繝ｻ繝ｻ・ｽ・｡c t繝ｻ繝ｻ・｣・ｰi nguy繝ｻ繝ｻ・ｽ・ｪn n繝ｻ繝ｻ・｣・ｰy.",
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
            throw new NotFoundHttpException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.");
        }
        return $section;
    }
    private function assertSectionBelongsToCourse(
        CourseSection $section,
        Course $course,
    ): void {
        if ((int) $section->course_id !== (int) $course->id) {
            throw new HttpException(422, "Tham s騾ｶ・ｻ郢晢ｽｻkh繝ｻ繝ｻ・ｽ・ｴng h騾ｶ・ｻ繝ｻ・｣p l騾ｶ・ｻ郢晢ｽｻ");
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
            throw new BusinessException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.", 404);
        }

        if ((int) $course->instructor_id !== (int) $instructorId) {
            throw new BusinessException(
                "B髯ゑｽｯ繝ｻ・｡n kh繝ｻ繝ｻ・ｽ・ｴng c繝ｻ繝ｻ・ｽ・ｳ quy騾ｶ・ｻ繝ｻ・ｽ thao t繝ｻ繝ｻ・ｽ・｡c t繝ｻ繝ｻ・｣・ｰi nguy繝ｻ繝ｻ・ｽ・ｪn n繝ｻ繝ｻ・｣・ｰy.",
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
                "Gi繝ｻ繝ｻ・ｽ・｡ khuy髯ゑｽｯ繝ｻ・ｿn m繝ｻ繝ｻ・ｽ・｣i kh繝ｻ繝ｻ・ｽ・ｴng 繝ｻ繝ｻ豌医・・ｰ騾ｶ・ｻ繝ｻ・｣c l騾ｶ・ｻ陞ｫ・ｾ h繝ｻ繝ｻ・ｽ・｡n gi繝ｻ繝ｻ・ｽ・｡ g騾ｶ・ｻ騾ｾ繝ｻ",
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
            throw new BusinessException("Danh m騾ｶ・ｻ繝ｻ・･c kh繝ｻ繝ｻ・ｽ・ｴng h騾ｶ・ｻ繝ｻ・｣p l騾ｶ・ｻ郢晢ｽｻ", 422);
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
            throw new BusinessException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.", 404);
        }

        if ((int) $course->instructor_id !== (int) $instructorId) {
            throw new BusinessException(
                "B髯ゑｽｯ繝ｻ・｡n kh繝ｻ繝ｻ・ｽ・ｴng c繝ｻ繝ｻ・ｽ・ｳ quy騾ｶ・ｻ繝ｻ・ｽ thao t繝ｻ繝ｻ・ｽ・｡c t繝ｻ繝ｻ・｣・ｰi nguy繝ｻ繝ｻ・ｽ・ｪn n繝ｻ繝ｻ・｣・ｰy.",
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
            throw new BusinessException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.", 404);
        }

        if (!$section->course) {
            throw new BusinessException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.", 404);
        }

        if ((int) $section->course->instructor_id !== (int) $instructorId) {
            throw new BusinessException(
                "B髯ゑｽｯ繝ｻ・｡n kh繝ｻ繝ｻ・ｽ・ｴng c繝ｻ繝ｻ・ｽ・ｳ quy騾ｶ・ｻ繝ｻ・ｽ thao t繝ｻ繝ｻ・ｽ・｡c t繝ｻ繝ｻ・｣・ｰi nguy繝ｻ繝ｻ・ｽ・ｪn n繝ｻ繝ｻ・｣・ｰy.",
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
            throw new BusinessException("Kh繝ｻ繝ｻ・ｽ・ｴng t繝ｻ繝ｻ・ｽ・ｬm th髯ゑｽｯ繝ｻ・･y d騾ｶ・ｻ繝ｻ・ｯ li騾ｶ・ｻ邱包ｽ｡.", 404);
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

    public function getRevenueReport(\App\Models\User $instructor, array $filters): array
    {
        $repository = app(\App\Repositories\Instructor\InstructorRevenueRepository::class);

        if (!empty($filters['course_id'])) {
            $courseId = (int) $filters['course_id'];

            if (!$repository->courseExists($courseId)) {
                throw new \App\Exceptions\BusinessException('Kh・・ｽｴng t・・ｽｬm th陂ｯ・･y d逶ｻ・ｯ li逶ｻ緕｡.', 404);
            }

            if (!$repository->instructorOwnsCourse((int) $instructor->id, $courseId)) {
                throw new \App\Exceptions\BusinessException('B陂ｯ・｡n kh・・ｽｴng c・・ｽｳ quy逶ｻ・ｽ xem d逶ｻ・ｯ li逶ｻ緕｡ kh・・ｽｳa h逶ｻ逧・n・・｣ｰy.', 403);
            }
        }

        return $repository->getRevenueReport((int) $instructor->id, $filters);
    }

    public function paginateInstructorQuizzes(User $instructor, array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $repository = app(\App\Repositories\Instructor\InstructorQuizRepository::class);

        if (!empty($filters['course_id'])) {
            $this->ensureCourseBelongsToInstructor((int) $filters['course_id'], (int) $instructor->id);
        }

        if (!empty($filters['lesson_id'])) {
            $lesson = $repository->findLessonWithCourse((int) $filters['lesson_id']);

            if (!$lesson || !$lesson->course) {
                throw new BusinessException('Khﾃｴng tﾃｬm th蘯･y d盻ｯ li盻㎡.', 404);
            }

            if ((int) $lesson->course->instructor_id !== (int) $instructor->id) {
                throw new BusinessException('B蘯｡n khﾃｴng ﾄ柁ｰ盻｣c thao tﾃ｡c quiz c盻ｧa khﾃｳa h盻皇 nﾃy.', 403);
            }
        }

        return $repository->paginateOwnedQuizzes((int) $instructor->id, $filters);
    }

    public function getInstructorQuiz(User $instructor, int $quizId): \App\Models\Quiz
    {
        $repository = app(\App\Repositories\Instructor\InstructorQuizRepository::class);

        $quiz = $repository->findOwnedQuiz((int) $instructor->id, $quizId);

        if (!$quiz) {
            throw new BusinessException('Khﾃｴng tﾃｬm th蘯･y d盻ｯ li盻㎡.', 404);
        }

        return $quiz;
    }

    public function createInstructorQuiz(User $instructor, array $data): \App\Models\Quiz
    {
        return DB::transaction(function () use ($instructor, $data): \App\Models\Quiz {
            $lesson = $this->getQuizLessonOwnedByInstructor((int) $data['lesson_id'], (int) $instructor->id);

            foreach ($data['questions'] as $question) {
                $this->assertQuizQuestionHasCorrectOption($question);
            }

            $quiz = \App\Models\Quiz::query()->create([
                'course_id' => (int) $lesson->course_id,
                'lesson_id' => (int) $lesson->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'passing_score' => $data['passing_score'] ?? 0,
                'status' => $data['status'] ?? 'draft',
            ]);

            $this->syncInstructorQuizQuestions($quiz, $data['questions']);

            return $quiz->refresh()->load([
                'course:id,instructor_id,title,status',
                'lesson:id,course_id,title,status',
                'questions.options',
            ]);
        });
    }

    public function updateInstructorQuiz(User $instructor, int $quizId, array $data): \App\Models\Quiz
    {
        return DB::transaction(function () use ($instructor, $quizId, $data): \App\Models\Quiz {
            $quiz = $this->getInstructorQuiz($instructor, $quizId);

            $updateData = [];

            if (array_key_exists('lesson_id', $data)) {
                $lesson = $this->getQuizLessonOwnedByInstructor((int) $data['lesson_id'], (int) $instructor->id);
                $updateData['lesson_id'] = (int) $lesson->id;
                $updateData['course_id'] = (int) $lesson->course_id;
            }

            foreach (['title', 'description', 'passing_score', 'status'] as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            if ($updateData !== []) {
                $quiz->update($updateData);
            }

            if (array_key_exists('questions', $data)) {
                foreach ($data['questions'] as $question) {
                    $this->assertQuizQuestionHasCorrectOption($question);
                }

                $this->syncInstructorQuizQuestions($quiz, $data['questions']);
            }

            return $quiz->refresh()->load([
                'course:id,instructor_id,title,status',
                'lesson:id,course_id,title,status',
                'questions.options',
            ]);
        });
    }

    public function deleteInstructorQuiz(User $instructor, int $quizId): void
    {
        DB::transaction(function () use ($instructor, $quizId): void {
            $quiz = $this->getInstructorQuiz($instructor, $quizId);
            $quiz->delete();
        });
    }

    private function getQuizLessonOwnedByInstructor(int $lessonId, int $instructorId): \App\Models\Lesson
    {
        $repository = app(\App\Repositories\Instructor\InstructorQuizRepository::class);

        $lesson = $repository->findLessonWithCourse($lessonId);

        if (!$lesson || !$lesson->course) {
            throw new BusinessException('Khﾃｴng tﾃｬm th蘯･y d盻ｯ li盻㎡.', 404);
        }

        if ((int) $lesson->course->instructor_id !== $instructorId) {
            throw new BusinessException('B蘯｡n khﾃｴng ﾄ柁ｰ盻｣c thao tﾃ｡c quiz c盻ｧa khﾃｳa h盻皇 nﾃy.', 403);
        }

        return $lesson;
    }

    private function assertQuizQuestionHasCorrectOption(array $question): void
    {
        $hasCorrectOption = collect($question['options'] ?? [])
            ->contains(fn (array $option): bool => (bool) ($option['is_correct'] ?? false));

        if (!$hasCorrectOption) {
            throw new BusinessException('M盻擁 cﾃ｢u h盻淑 ph蘯｣i cﾃｳ ﾃｭt nh蘯･t m盻冲 ﾄ妥｡p ﾃ｡n ﾄ妥ｺng.', 422);
        }
    }

    private function syncInstructorQuizQuestions(\App\Models\Quiz $quiz, array $questions): void
    {
        $oldQuestionIds = \App\Models\QuizQuestion::query()
            ->where('quiz_id', $quiz->id)
            ->pluck('id')
            ->all();

        if ($oldQuestionIds !== []) {
            \App\Models\QuizOption::query()
                ->whereIn('question_id', $oldQuestionIds)
                ->delete();

            \App\Models\QuizQuestion::query()
                ->whereIn('id', $oldQuestionIds)
                ->delete();
        }

        foreach (array_values($questions) as $questionIndex => $questionData) {
            $question = \App\Models\QuizQuestion::query()->create([
                'quiz_id' => (int) $quiz->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'score' => $questionData['score'],
                'sort_order' => $questionIndex + 1,
                'explanation' => $questionData['explanation'] ?? null,
            ]);

            foreach (array_values($questionData['options']) as $optionIndex => $optionData) {
                \App\Models\QuizOption::query()->create([
                    'question_id' => (int) $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => (bool) $optionData['is_correct'],
                    'sort_order' => $optionIndex + 1,
                ]);
            }
        }
    }

    public function createWithdrawRequest(User $instructor, array $data): \App\Models\WithdrawRequest
    {
        return DB::transaction(function () use ($instructor, $data): \App\Models\WithdrawRequest {
            $repository = app(\App\Repositories\Instructor\InstructorWithdrawRepository::class);

            $payoutAccount = $repository->findActivePayoutAccountForUser(
                (int) $data['payout_account_id'],
                (int) $instructor->id,
            );

            if (!$payoutAccount) {
                throw new BusinessException('Tài khoản nhận tiền không hợp lệ.', 403);
            }

            $availableRevenueAmount = $repository->getAvailableRevenueAmount((int) $instructor->id);
            $reservedWithdrawAmount = $repository->getReservedWithdrawAmount((int) $instructor->id);
            $availableBalance = max($availableRevenueAmount - $reservedWithdrawAmount, 0);
            $amount = (float) $data['amount'];

            if ($amount > $availableBalance) {
                throw new BusinessException('Số tiền yêu cầu vượt số dư khả dụng.', 422, [
                    'amount' => ['Số tiền yêu cầu vượt số dư khả dụng.'],
                ]);
            }

            $withdrawRequest = $repository->createWithdrawRequest([
                'user_id' => (int) $instructor->id,
                'payout_account_id' => (int) $payoutAccount->id,
                'amount' => $amount,
                'status' => 'pending',
                'requested_at' => now(),
                'approved_at' => null,
                'paid_at' => null,
                'rejected_reason' => null,
                'provider_payout_id' => null,
                'account_number_snapshot' => $payoutAccount->account_number,
                'account_name_snapshot' => $payoutAccount->account_name,
            ]);

            $withdrawRequest->setAttribute(
                'available_balance_before',
                number_format($availableBalance, 2, '.', ''),
            );

            $withdrawRequest->setAttribute(
                'available_balance_after',
                number_format($availableBalance - $amount, 2, '.', ''),
            );

            return $withdrawRequest->load('payoutAccount');
        });
    }

    public function getCourseLearners(int $courseId, int $instructorId, array $filters)
    {
        $course = \DB::table('courses')->where('id', $courseId)->whereNull('deleted_at')->first();

        if (!$course) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Không tìm thấy dữ liệu.');
        }

        if ($course->instructor_id !== $instructorId) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Bạn không có quyền xem dữ liệu khóa học này.');
        }

        $query = \DB::table('enrollments')
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->where('enrollments.course_id', $courseId)
            ->where('users.role', 'learner')
            ->select(
                'users.id as learner_id',
                'users.full_name',
                'users.email',
                'users.phone',
                'users.status as learner_status',
                'enrollments.id as enrollment_id',
                'enrollments.status as enrollment_status',
                'enrollments.last_accessed_at',
                'enrollments.completed_at'
            );
        
        if (\Schema::hasColumn('enrollments', 'enrolled_at')) {
            $query->addSelect('enrollments.enrolled_at');
        } elseif (\Schema::hasColumn('enrollments', 'created_at')) {
            $query->addSelect('enrollments.created_at as enrolled_at');
        }

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('users.full_name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // Status
        if (!empty($filters['status'])) {
            $query->where('enrollments.status', $filters['status']);
        }

        // Progress Calculation using subquery if tables exist
        if (\Schema::hasTable('lessons') && \Schema::hasTable('lesson_progress')) {
            $totalLessonsSubquery = \DB::table('lessons')
                ->where('course_id', $courseId)
                ->selectRaw('count(*)')
                ->toSql();

            $completedLessonsSubquery = \DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->where('lessons.course_id', $courseId)
                ->whereColumn('lesson_progress.user_id', 'users.id')
                ->where('lesson_progress.status', 'completed')
                ->selectRaw('count(*)')
                ->toSql();
            
            // Avoid division by zero
            $query->selectRaw("
                CASE 
                    WHEN ($totalLessonsSubquery) > 0 THEN 
                        CAST(($completedLessonsSubquery) AS FLOAT) / ($totalLessonsSubquery) * 100
                    ELSE 0 
                END as progress_percent
            ", [\DB::raw($courseId), \DB::raw($courseId)]);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'last_accessed_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $sortMap = [
            'id' => 'users.id',
            'full_name' => 'users.full_name',
            'email' => 'users.email',
            'status' => 'enrollments.status',
            'last_accessed_at' => 'enrollments.last_accessed_at',
            'completed_at' => 'enrollments.completed_at',
            'created_at' => 'enrollments.created_at',
        ];

        $orderCol = $sortMap[$sortBy] ?? 'enrollments.last_accessed_at';
        if ($orderCol === 'enrollments.created_at' && \Schema::hasColumn('enrollments', 'enrolled_at')) {
            $orderCol = 'enrollments.enrolled_at';
        }
        $query->orderBy($orderCol, $sortDirection);

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }
}
