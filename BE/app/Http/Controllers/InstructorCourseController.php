<?php
namespace App\Http\Controllers;
use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Requests\Instructor\UploadLessonVideoRequest;
use App\Http\Resources\Instructor\InstructorCourseResource;
use App\Http\Resources\Instructor\InstructorLessonResource;
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
        return response()->json([
            'success' => true,
            'message' => 'Tạo khóa học thành công.',
            'data' => new InstructorCourseResource($course),
        ], 201);
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
            new InstructorLessonResource($lesson),
            'Upload video bài học thành công.',
            201
        );
    }
}