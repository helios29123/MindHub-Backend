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
use Illuminate\Support\Facades\Schema;
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
        return DB::transaction(function () use ($instructor, $validatedData): Course {
            $categoryIds = $validatedData['category_ids'] ?? [];
            unset($validatedData['category_ids']);

            $this->removeForbiddenFields($validatedData);
            $this->validateCategoryIds($categoryIds);
            $this->processCoursePriceData($validatedData);

            $title = trim((string) $validatedData['title']);
            $slugSource = $validatedData['slug'] ?? $title;

            $courseData = array_merge($validatedData, [
                'instructor_id' => $instructor->id,
                'title' => $title,
                'slug' => $this->makeUniqueCourseSlug((string) $slugSource),
                'status' => 'draft',
                'is_featured' => false,
                'published_at' => null,
                'admin_reject_reason' => null,
                'language' => $validatedData['language'] ?? 'vi',
                'course_level' => $validatedData['course_level'] ?? 'beginner',
            ]);

            $course = $this->instructorCourseRepository->create($courseData);

            if (!empty($categoryIds)) {
                $this->instructorCourseRepository->syncCategories($course, $categoryIds);
            }

            return $this->instructorCourseRepository->findWithCategories((int) $course->id);
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
                    "Bài học đang ở trạng thái ẩn không thể xem trước.",
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
                throw new NotFoundHttpException("Không tìm thấy khóa học.");
            }
            if ((int) $course->instructor_id !== (int) $instructor->id) {
                throw new BusinessException(
                    "Bạn không có quyền gửi duyệt khóa học này.",
                    403,
                );
            }
            if (!$this->courseCanBeSubmitted($course)) {
                throw new BusinessException(
                    "Khóa học chưa đủ điều kiện để gửi duyệt. Vui lòng hoàn thiện thông tin cơ bản, danh mục, chương học và bài học.",
                    422,
                );
            }
            $updatedCourse = $this->instructorCourseRepository->markAsPendingReview(
                $course,
            );

            // Send Email to Admin & Create DB Notification for Admin
            try {
                $adminEmail = env('ADMIN_EMAIL', config('mail.admin_address', 'dominhdang3010@gmail.com'));
                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(
                    new \App\Mail\CourseSubmittedForReviewMail($instructor, $updatedCourse)
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send admin course review email: ' . $e->getMessage());
            }

            try {
                $adminUsers = User::where('role', 'admin')->get();
                foreach ($adminUsers as $admin) {
                    \App\Models\Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'course_submitted',
                        'title' => 'Yêu cầu duyệt khóa học mới',
                        'message' => "Giảng viên {$instructor->full_name} đã gửi yêu cầu duyệt khóa học: {$updatedCourse->title}",
                        'action_url' => '/admin/courses',
                        'channel' => 'web',
                    ]);
                }
            } catch (\Throwable $e) {
            }

            return $updatedCourse;
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
            throw new NotFoundHttpException("Không tìm thấy khóa học.");
        }
        if ((int) $course->instructor_id !== (int) $instructor->id) {
            throw new BusinessException(
                "Bạn không có quyền xem thông tin khóa học này.",
                403,
            );
        }
        if ($course->status !== "rejected") {
            throw new NotFoundHttpException("Khóa học không ở trạng thái bị từ chối.");
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
                "course_level",
                "language",
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
        if ($lessonCount === 0) {
            return false;
        }

        foreach ($course->sections as $section) {
            foreach ($section->lessons as $lesson) {
                if (strtolower((string) $lesson->lesson_type) === 'video') {
                    $videoUrl = trim((string) ($lesson->video_url ?? ''));
                    if ($videoUrl === '' || str_starts_with($videoUrl, 'blob:')) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function findOwnedLessonOrFail(
        User $instructor,
        int $lessonId,
    ): Lesson {
        $lesson = $this->instructorLessonRepository->findByIdWithCourse(
            $lessonId,
        );
        if (!$lesson) {
            throw new NotFoundHttpException("Không tìm thấy bài học.");
        }
        if (
            !$lesson->course ||
            (int) $lesson->course->instructor_id !== (int) $instructor->id
        ) {
            throw new AccessDeniedHttpException(
                "Bạn không có quyền truy cập bài học này.",
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
            throw new NotFoundHttpException("Không tìm thấy khóa học.");
        }
        if ((int) $course->instructor_id !== (int) $instructor->id) {
            throw new AccessDeniedHttpException(
                "Bạn không có quyền thao tác trên khóa học này.",
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
            throw new NotFoundHttpException("Không tìm thấy chương học.");
        }
        return $section;
    }

    private function assertSectionBelongsToCourse(
        CourseSection $section,
        Course $course,
    ): void {
        if ((int) $section->course_id !== (int) $course->id) {
            throw new HttpException(422, "Chương học không thuộc khóa học này.");
        }
    }

    public function updateCourse(
        int $courseId,
        int $instructorId,
        array $data,
    ): Course {
        $course = Course::query()->where("id", $courseId)->first();

        if (!$course) {
            throw new BusinessException("Không tìm thấy khóa học.", 404);
        }

        if ((int) $course->instructor_id !== (int) $instructorId) {
            throw new BusinessException(
                "Bạn không có quyền cập nhật khóa học này.",
                403,
            );
        }

        $this->processCoursePriceData($data, $course);
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

    private function processCoursePriceData(array &$data, ?Course $existingCourse = null): void
    {
        $hasPriceKey = array_key_exists('original_price', $data) || array_key_exists('price', $data);
        if ($hasPriceKey || !$existingCourse) {
            $originalPrice = array_key_exists('original_price', $data)
                ? (float) $data['original_price']
                : (array_key_exists('price', $data) ? (float) $data['price'] : ($existingCourse ? (float) $existingCourse->price : 0.0));

            if ($originalPrice < 0) {
                $originalPrice = 0.0;
            }
            $data['price'] = $originalPrice;
        } else {
            $originalPrice = (float) $existingCourse->price;
        }

        unset($data['original_price']);

        $hasDiscount = false;
        if (array_key_exists('has_discount', $data)) {
            $hasDiscount = filter_var($data['has_discount'], FILTER_VALIDATE_BOOLEAN);
        } elseif (array_key_exists('discount_percent', $data)) {
            $hasDiscount = $data['discount_percent'] !== null && (int) $data['discount_percent'] > 0;
        } elseif ($existingCourse) {
            $hasDiscount = $existingCourse->discount_percent !== null && (int) $existingCourse->discount_percent > 0;
        }

        unset($data['has_discount']);

        if ($hasDiscount) {
            $discountPercent = array_key_exists('discount_percent', $data)
                ? ($data['discount_percent'] !== null ? (int) $data['discount_percent'] : ($existingCourse ? (int) $existingCourse->discount_percent : 0))
                : ($existingCourse ? (int) $existingCourse->discount_percent : 0);

            if ($discountPercent < 1 || $discountPercent > 99) {
                throw new BusinessException('Phần trăm giảm giá phải từ 1% đến 99%.', 422);
            }

            $data['discount_percent'] = $discountPercent;
            $data['sale_price'] = (float) round($originalPrice * (100 - $discountPercent) / 100);
        } else {
            $data['discount_percent'] = null;
            $data['sale_price'] = $originalPrice;
        }
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
            ->count();

        if ($validCategoryCount !== count(array_unique($categoryIds))) {
            throw new BusinessException("Danh mục đã chọn không hợp lệ.", 422);
        }
    }

    private function removeForbiddenFields(array &$data): void
    {
        unset(
            $data["id"],
            $data["instructor_id"],
            $data["is_featured"],
            
            $data["published_at"],
            $data["admin_reject_reason"],
            
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
            $data["sort_order"] === null ||
            CourseSection::query()->where('course_id', $course->id)->where('sort_order', $data['sort_order'])->exists()
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
            throw new BusinessException("Không tìm thấy khóa học.", 404);
        }

        if ((int) $course->instructor_id !== (int) $instructorId) {
            throw new BusinessException(
                "Bạn không có quyền thao tác trên khóa học này.",
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
            throw new BusinessException("Không tìm thấy chương học.", 404);
        }

        if (!$section->course) {
            throw new BusinessException("Không tìm thấy thông tin khóa học.", 404);
        }

        if ((int) $section->course->instructor_id !== (int) $instructorId) {
            throw new BusinessException(
                "Bạn không có quyền thao tác trên chương học này.",
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
            throw new BusinessException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・", 404);
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
                throw new \App\Exceptions\BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 404);
            }

            if (!$repository->instructorOwnsCourse((int) $instructor->id, $courseId)) {
                throw new \App\Exceptions\BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 403);
            }
        }

        return $repository->getRevenueReport((int) $instructor->id, $filters);
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
                throw new BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 403);
            }

            $availableRevenueAmount = $repository->getAvailableRevenueAmount((int) $instructor->id);
            $reservedWithdrawAmount = $repository->getReservedWithdrawAmount((int) $instructor->id);
            $availableBalance = max($availableRevenueAmount - $reservedWithdrawAmount, 0);
            $amount = (float) $data['amount'];

            if ($amount > $availableBalance) {
                throw new BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 422, [
                    'amount' => ['D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・'],
                ]);
            }

            $withdrawRequest = $repository->createWithdrawRequest([
                'user_id' => (int) $instructor->id,
                'payout_account_id' => (int) $payoutAccount->id,
                'amount' => $amount,
                'status' => 'pending',
                'requested_at' => now(),
                'reviewed_by' => null,
                'paid_at' => null,
                'admin_reject_reason' => null,
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
        $course = DB::table('courses')->where('id', $courseId)->first();

        if (!$course) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Không tìm thấy khóa học.');
        }

        if ((int) $course->instructor_id !== (int) $instructorId) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Bạn không có quyền xem thông tin khóa học này.');
        }

        $query = DB::table('enrollments')
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

        if (Schema::hasColumn('enrollments', 'enrolled_at')) {
            $query->addSelect('enrollments.enrolled_at');
        } elseif (Schema::hasColumn('enrollments', 'created_at')) {
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

        if (Schema::hasColumn('enrollments', 'progress_percent')) {
            $query->addSelect('enrollments.progress_percent');
        } else {
            $query->selectRaw('0 as progress_percent');
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
        if ($orderCol === 'enrollments.created_at' && Schema::hasColumn('enrollments', 'enrolled_at')) {
            $orderCol = 'enrollments.enrolled_at';
        }
        $query->orderBy($orderCol, $sortDirection);

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    public function paginateCourses(int $instructorId, array $filters): LengthAwarePaginator
    {
        return $this->instructorCourseRepository->paginateCourses($instructorId, $filters);
    }

    public function createDraftCourse(User $instructor, array $data): Course
    {
        return DB::transaction(function () use ($instructor, $data): Course {
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids']);

            $this->removeForbiddenFields($data);
            $this->validateCategoryIds($categoryIds);
            $this->processCoursePriceData($data);

            $title = trim((string) ($data['title'] ?? ''));

            if ($title === '') {
                $title = 'Khóa học chưa đặt tên';
            }

            $slugSource = $data['slug'] ?? ($title . '-' . uniqid());

            $courseData = array_merge($data, [
                'instructor_id' => $instructor->id,
                'title' => $title,
                'slug' => $this->makeUniqueCourseSlug((string) $slugSource),
                'price' => $data['price'] ?? 0,
                'status' => 'draft',
                'is_featured' => false,
                'published_at' => null,
                'admin_reject_reason' => null,
                'language' => $data['language'] ?? 'vi',
                'course_level' => $data['course_level'] ?? 'beginner',
            ]);

            $course = $this->instructorCourseRepository->create($courseData);

            if (!empty($categoryIds)) {
                $this->instructorCourseRepository->syncCategories($course, $categoryIds);
            }

            return $this->instructorCourseRepository->findWithCategories((int) $course->id);
        });
    }

    public function getCourseDetail(User $instructor, int $courseId): Course
    {
        $course = $this->instructorCourseRepository->findOwnedCourseForDetail(
            $courseId,
            (int) $instructor->id
        );

        if (!$course) {
            throw new BusinessException('Không tìm thấy khóa học hoặc bạn không có quyền xem.', 404);
        }

        return $course;
    }

    public function getCourseContent(User $instructor, int $courseId): Course
    {
        $course = $this->instructorCourseRepository->findOwnedCourseForContent(
            $courseId,
            (int) $instructor->id
        );

        if (!$course) {
            throw new BusinessException('Không tìm thấy khóa học hoặc bạn không có quyền xem.', 404);
        }

        return $course;
    }

    public function updateCourseDraft(User $instructor, int $courseId, array $data): Course
    {
        $course = Course::query()
            ->where('id', $courseId)
            ->where('instructor_id', (int) $instructor->id)
            ->first();

        if (!$course) {
            throw new BusinessException('Không tìm thấy khóa học hoặc bạn không có quyền cập nhật.', 404);
        }

        if (!in_array($course->status, ['draft', 'rejected'], true)) {
            throw new BusinessException('Chỉ được lưu nháp khóa học đang hoàn thiện hoặc bị từ chối.', 409);
        }

        $categoryIds = null;

        if (array_key_exists('category_ids', $data)) {
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids']);
            $this->validateCategoryIds($categoryIds);
        }

        $this->removeForbiddenFields($data);

        if (array_key_exists('title', $data)) {
            $title = trim((string) $data['title']);

            if ($title !== '') {
                $data['title'] = $title;
            } else {
                unset($data['title']);
            }
        }

        if (array_key_exists('slug', $data) && trim((string) $data['slug']) !== '') {
            $data['slug'] = $this->makeUniqueCourseSlug((string) $data['slug'], (int) $course->id);
        } elseif (array_key_exists('slug', $data)) {
            unset($data['slug']);
        }

        $this->processCoursePriceData($data, $course);
        $this->validateSalePrice($course, $data);

        return DB::transaction(function () use ($course, $data, $categoryIds): Course {
            return $this->instructorCourseRepository->updateCourseWithCategories(
                $course,
                $data,
                $categoryIds
            );
        });
    }

    private function makeUniqueCourseSlug(string $source, ?int $ignoreCourseId = null): string
    {
        $base = Str::slug($source);

        if ($base === '') {
            $base = 'khoa-hoc';
        }

        $slug = $base;
        $counter = 1;

        while (
            Course::query()
            ->where('slug', $slug)
            ->when($ignoreCourseId !== null, fn($query) => $query->where('id', '!=', $ignoreCourseId))
            ->exists()
        ) {
            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }

    public function hideCourse(User $instructor, int $courseId): Course
    {
        $course = Course::query()
            ->where('id', $courseId)
            ->where('instructor_id', $instructor->id)
            ->first();

        if (! $course) {
            throw new BusinessException('Khóa học không tồn tại hoặc bạn không có quyền thao tác.', 404);
        }

        if ($course->status !== 'published') {
            throw new BusinessException('Chỉ khóa học published mới được chuyển sang hidden.', 409);
        }

        return DB::transaction(function () use ($course): Course {
            $course->update(['status' => 'hidden']);
            return $course->fresh();
        });
    }

    public function unhideCourse(User $instructor, int $courseId): Course
    {
        $course = Course::query()
            ->where('id', $courseId)
            ->where('instructor_id', $instructor->id)
            ->first();

        if (! $course) {
            throw new BusinessException('Khóa học không tồn tại hoặc bạn không có quyền thao tác.', 404);
        }

        if ($course->status !== 'hidden') {
            throw new BusinessException('Chỉ khóa học hidden mới được chuyển lại published.', 409);
        }

        return DB::transaction(function () use ($course): Course {
            $course->update([
                'status' => 'published',
                'published_at' => $course->published_at ?? now(),
            ]);
            return $course->fresh();
        });
    }

}
