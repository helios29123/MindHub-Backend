<?php
namespace App\Http\Controllers;

use App\Exceptions\BusinessException;

use App\Http\Requests\Instructor\SubmitForReviewRequest;
use App\Http\Requests\Instructor\ManageLessonsRequest;
use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Requests\Instructor\StoreLessonRequest;
use App\Http\Requests\Instructor\UpdateLessonRequest;
use App\Http\Requests\Instructor\UploadLessonVideoRequest;
use App\Http\Requests\Instructor\UploadLessonAssetRequest;
use App\Http\Resources\Instructor\InstructorCourseResource;
use App\Http\Resources\Instructor\LessonResource;
use App\Http\Resources\Instructor\LessonAssetResource;
use App\Http\Resources\Instructor\ReviewNoteResource;
use App\Services\Instructor\InstructorCourseService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
final class InstructorCourseController extends Controller
{
    public function __construct(
        private readonly InstructorCourseService $instructorCourseService
    ) {
    }
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->instructorCourseService->createCourse(
            $request->user(),
            $request->validated()
        );
        return ApiResponse::success(
            new InstructorCourseResource($course),
            'Tạo khóa học thành công.',
            201
        );
    }
    public function indexLessons(ManageLessonsRequest $request): JsonResponse
    {
        $lessons = $this->instructorCourseService->paginateLessons(
            $request->user(),
            $request->validated()
        );
        return ApiResponse::paginated(
            LessonResource::collection($lessons),
            $lessons,
            'Lấy danh sách bài học thành công.'
        );
    }
    public function storeLesson(StoreLessonRequest $request): JsonResponse
    {
        $lesson = $this->instructorCourseService->createLesson(
            $request->user(),
            $request->validated()
        );
        return ApiResponse::success(
            new LessonResource($lesson),
            'Tạo bài học thành công.',
            201
        );
    }
    public function showLesson(int $id): JsonResponse
    {
        $lesson = $this->instructorCourseService->getLesson(
            request()->user(),
            $id
        );
        return ApiResponse::success(
            new LessonResource($lesson),
            'Lấy chi tiết bài học thành công.'
        );
    }
    public function updateLesson(UpdateLessonRequest $request, int $id): JsonResponse
    {
        $lesson = $this->instructorCourseService->updateLesson(
            $request->user(),
            $id,
            $request->validated()
        );
        return ApiResponse::success(
            new LessonResource($lesson),
            'Cập nhật bài học thành công.'
        );
    }
    public function destroyLesson(int $id): JsonResponse
    {
        $this->instructorCourseService->deleteLesson(
            request()->user(),
            $id
        );
        return ApiResponse::success(
            [],
            'Xóa bài học thành công.'
        );
    }
    public function uploadVideo(UploadLessonVideoRequest $request, int $id): JsonResponse
    {
        $lesson = $this->instructorCourseService->uploadLessonVideo(
            $request->user(),
            $id,
            $request->validated(),
            $request->file('video')
        );
        return ApiResponse::success(
            new LessonResource($lesson),
            'Upload video bài học thành công.',
            201
        );
    }

    public function uploadAsset(UploadLessonAssetRequest $request, int $id): JsonResponse
    {
        $asset = $this->instructorCourseService->uploadLessonAsset(
            $request->user(),
            $id,
            $request->validated(),
            $request->file('file')
        );
        return ApiResponse::success(
            new LessonAssetResource($asset),
            'Thao tác thành công',
            201
        );
    }    public function submitForReview(SubmitForReviewRequest $request, int $id): JsonResponse
    {
        $course = $this->instructorCourseService->submitForReview(
            $request->user(),
            $id
        );
        return ApiResponse::success(
            new InstructorCourseResource($course),
            'Thao tác thành công',
            201
        );
    }

    public function reviewNotes(string $id): JsonResponse
    {
        if (! ctype_digit($id) || (int) $id < 1) {
            throw new BusinessException('Tham số không hợp lệ.', 422);
        }
        $course = $this->instructorCourseService->getRejectedReviewNotes(
            request()->user(),
            (int) $id
        );
        return ApiResponse::success(
            new ReviewNoteResource($course),
            'Lấy dữ liệu thành công'
        );
    }
}