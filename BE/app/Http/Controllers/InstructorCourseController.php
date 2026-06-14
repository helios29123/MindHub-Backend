<?php
namespace App\Http\Controllers;

use App\Http\Requests\Instructor\InstructorRevenueQueryRequest;
use App\Http\Resources\Instructor\InstructorRevenueResource;
use App\Exceptions\BusinessException;

use App\Http\Requests\Instructor\SubmitForReviewRequest;
use App\Http\Requests\Instructor\ManageLessonsRequest;
use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Requests\Instructor\StoreLessonRequest;
use App\Http\Requests\Instructor\TogglePreviewRequest;
use App\Http\Requests\Instructor\UpdateLessonRequest;
use App\Http\Requests\Instructor\UpdateCourseRequest;
use App\Http\Requests\Instructor\UploadLessonVideoRequest;
use App\Http\Requests\Instructor\UploadLessonAssetRequest;
use App\Http\Resources\Instructor\InstructorCourseResource;
use App\Http\Resources\Instructor\LessonResource;
use App\Http\Resources\Instructor\LessonAssetResource;
use App\Http\Resources\Instructor\ReviewNoteResource;
use App\Services\Instructor\InstructorCourseService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Instructor\SectionQueryRequest;
use App\Http\Requests\Instructor\StoreSectionRequest;
use App\Http\Requests\Instructor\UpdateSectionRequest;
use App\Http\Resources\Instructor\InstructorSectionResource;
use Illuminate\Http\Request;

final class InstructorCourseController extends Controller
{
    public function __construct(
        private readonly InstructorCourseService $instructorCourseService,
    ) {}
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->instructorCourseService->createCourse(
            $request->user(),
            $request->validated(),
        );
        return ApiResponse::success(
            new InstructorCourseResource($course),
            "T鬮ｯ繧托ｽｽ・ｯ郢晢ｽｻ繝ｻ・｡o kh郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｳa h鬨ｾ・ｶ繝ｻ・ｻ鬨ｾ・ｧ郢晢ｽｻth郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng.",
            201,
        );
    }
    public function indexLessons(ManageLessonsRequest $request): JsonResponse
    {
        $lessons = $this->instructorCourseService->paginateLessons(
            $request->user(),
            $request->validated(),
        );
        return ApiResponse::paginated(
            LessonResource::collection($lessons),
            $lessons,
            "L鬮ｯ繧托ｽｽ・ｯ郢晢ｽｻ繝ｻ・･y danh s郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡ch b郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰi h鬨ｾ・ｶ繝ｻ・ｻ鬨ｾ・ｧ郢晢ｽｻth郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng.",
        );
    }
    public function storeLesson(StoreLessonRequest $request): JsonResponse
    {
        $lesson = $this->instructorCourseService->createLesson(
            $request->user(),
            $request->validated(),
        );
        return ApiResponse::success(
            new LessonResource($lesson),
            "T鬮ｯ繧托ｽｽ・ｯ郢晢ｽｻ繝ｻ・｡o b郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰi h鬨ｾ・ｶ繝ｻ・ｻ鬨ｾ・ｧ郢晢ｽｻth郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng.",
            201,
        );
    }
    public function showLesson(int $id): JsonResponse
    {
        $lesson = $this->instructorCourseService->getLesson(
            request()->user(),
            $id,
        );
        return ApiResponse::success(
            new LessonResource($lesson),
            "L鬮ｯ繧托ｽｽ・ｯ郢晢ｽｻ繝ｻ・･y chi ti鬮ｯ繧托ｽｽ・ｯ郢晢ｽｻ繝ｻ・ｿt b郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰi h鬨ｾ・ｶ繝ｻ・ｻ鬨ｾ・ｧ郢晢ｽｻth郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng.",
        );
    }
    public function updateLesson(
        UpdateLessonRequest $request,
        int $id,
    ): JsonResponse {
        $lesson = $this->instructorCourseService->updateLesson(
            $request->user(),
            $id,
            $request->validated(),
        );
        return ApiResponse::success(
            new LessonResource($lesson),
            "C鬮ｯ繧托ｽｽ・ｯ郢晢ｽｻ繝ｻ・ｭp nh鬮ｯ繧托ｽｽ・ｯ郢晢ｽｻ繝ｻ・ｭt b郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰi h鬨ｾ・ｶ繝ｻ・ｻ鬨ｾ・ｧ郢晢ｽｻth郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng.",
        );
    }
    public function togglePreview(
        TogglePreviewRequest $request,
        int $id,
    ): JsonResponse {
        $validatedData = $request->validated();
        $lesson = $this->instructorCourseService->toggleLessonPreview(
            $request->user(),
            $id,
            (bool) $validatedData["is_preview"],
        );
        return ApiResponse::success(
            new LessonResource($lesson),
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
        );
    }
    public function destroyLesson(int $id): JsonResponse
    {
        $this->instructorCourseService->deleteLesson(request()->user(), $id);
        return ApiResponse::success([], "X郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｳa b郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰi h鬨ｾ・ｶ繝ｻ・ｻ鬨ｾ・ｧ郢晢ｽｻth郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng.");
    }
    public function uploadVideo(
        UploadLessonVideoRequest $request,
        int $id,
    ): JsonResponse {
        $lesson = $this->instructorCourseService->uploadLessonVideo(
            $request->user(),
            $id,
            $request->validated(),
            $request->file("video"),
        );
        return ApiResponse::success(
            new LessonResource($lesson),
            "Upload video b盻・l盻擁",
            201,
        );
    }

    public function uploadAsset(
        UploadLessonAssetRequest $request,
        int $id,
    ): JsonResponse {
        $asset = $this->instructorCourseService->uploadLessonAsset(
            $request->user(),
            $id,
            $request->validated(),
            $request->file("file"),
        );
        return ApiResponse::success(
            new LessonAssetResource($asset),
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
            201,
        );
    }

    public function submitForReview(
        SubmitForReviewRequest $request,
        int $id,
    ): JsonResponse {
        $course = $this->instructorCourseService->submitForReview(
            $request->user(),
            $id,
        );
        return ApiResponse::success(
            new InstructorCourseResource($course),
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
            201,
        );
    }

    public function reviewNotes(string $id): JsonResponse
    {
        if (!ctype_digit($id) || (int) $id < 1) {
            throw new BusinessException("Tham s鬨ｾ・ｶ繝ｻ・ｻ驛｢譎｢・ｽ・ｻkh郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng h鬨ｾ・ｶ繝ｻ・ｻ郢晢ｽｻ繝ｻ・｣p l鬨ｾ・ｶ繝ｻ・ｻ驛｢譎｢・ｽ・ｻ", 422);
        }
        $course = $this->instructorCourseService->getRejectedReviewNotes(
            request()->user(),
            (int) $id,
        );
        return ApiResponse::success(
            new ReviewNoteResource($course),
            "L鬮ｯ繧托ｽｽ・ｯ郢晢ｽｻ繝ｻ・･y d鬨ｾ・ｶ繝ｻ・ｻ郢晢ｽｻ繝ｻ・ｯ li鬨ｾ・ｶ繝ｻ・ｻ驍ｱ蛹・ｽｽ・｡ th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
        );
    }

    public function update(UpdateCourseRequest $request, int $id): JsonResponse
    {
        $course = $this->instructorCourseService->updateCourse(
            $id,
            $request->user()->id,
            $request->validated(),
        );

        return ApiResponse::success(
            new InstructorCourseResource($course),
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
            200,
        );
    }

    public function sections(SectionQueryRequest $request): JsonResponse
    {
        $sections = $this->instructorCourseService->getSections(
            $request->validated(),
            $request->user()->id,
        );

        return ApiResponse::paginated(
            InstructorSectionResource::collection($sections),
            $sections,
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
        );
    }

    public function showSection(int $id, Request $request): JsonResponse
    {
        $section = $this->instructorCourseService->getSection(
            $id,
            $request->user()->id,
        );

        return ApiResponse::success(
            new InstructorSectionResource($section),
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
            200,
        );
    }

    public function storeSection(StoreSectionRequest $request): JsonResponse
    {
        $section = $this->instructorCourseService->createSection(
            $request->validated(),
            $request->user()->id,
        );

        return ApiResponse::success(
            new InstructorSectionResource($section),
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
            201,
        );
    }

    public function updateSection(
        UpdateSectionRequest $request,
        int $id,
    ): JsonResponse {
        $section = $this->instructorCourseService->updateSection(
            $id,
            $request->validated(),
            $request->user()->id,
        );

        return ApiResponse::success(
            new InstructorSectionResource($section),
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
            200,
        );
    }

    public function deleteSection(Request $request, int $id): JsonResponse
    {
        $this->instructorCourseService->deleteSection(
            $id,
            $request->user()->id,
        );

        return ApiResponse::success(null, "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng", 200);
    }

    public function profile(Request $request): JsonResponse
    {
        $profile = $this->instructorCourseService->getInstructorProfile($request->user()->id);

        return ApiResponse::success(
            new \App\Http\Resources\Instructor\InstructorProfileResource($profile),
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
            200
        );
    }

    public function updateProfile(\App\Http\Requests\Instructor\UpdateInstructorProfileRequest $request): JsonResponse
    {
        $profile = $this->instructorCourseService->updateInstructorProfile(
            $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success(
            new \App\Http\Resources\Instructor\InstructorProfileResource($profile),
            "Thao t郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・｡c th郢晢ｽｻ郢晢ｽｻ繝ｻ・｣繝ｻ・ｰnh c郢晢ｽｻ郢晢ｽｻ繝ｻ・ｽ繝ｻ・ｴng",
            200
        );
    }

    public function revenue(InstructorRevenueQueryRequest $request)
    {
        $report = $this->instructorCourseService->getRevenueReport(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => "Thao t\u{00E1}c th\u{00E0}nh c\u{00F4}ng",
            'data' => (new InstructorRevenueResource($report))->resolve($request),
        ], 200);
    }
}
