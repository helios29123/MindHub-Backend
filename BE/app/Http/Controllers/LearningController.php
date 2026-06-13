<?php

namespace App\Http\Controllers;

use App\Http\Requests\Learning\MyCoursesRequest;
use App\Http\Resources\Learning\MyCourseResource;
use App\Services\LearningService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\Learning\LearningLessonResource;

final class LearningController extends Controller
{
    public function __construct(
        private readonly LearningService $learningService
    ) {
    }

    /**
     * Get the list of purchased courses for the authenticated learner.
     *
     * @param MyCoursesRequest $request
     * @return JsonResponse
     */
    public function myCourses(MyCoursesRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $enrollments = $this->learningService->getPurchasedCourses(
            $user,
            $request->validated()
        );

        return ApiResponse::paginated(
            MyCourseResource::collection($enrollments),
            $enrollments,
            'Lấy danh sách khóa học đã mua thành công.'
        );
    }

    /**
     * Show lesson details and record learning progress for the user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function showLesson(int $id): JsonResponse
    {
        $user = request()->user();
        
        $details = $this->learningService->getLessonDetails($user, $id);

        return ApiResponse::success([
            'course' => [
                'id' => $details['course']->id,
                'title' => $details['course']->title,
                'slug' => $details['course']->slug,
            ],
            'lesson' => new LearningLessonResource($details['lesson']),
            'progress' => [
                'status' => $details['progress']->status,
                'started_at' => $details['progress']->started_at ? $details['progress']->started_at->toISOString() : null,
                'completed_at' => $details['progress']->completed_at ? $details['progress']->completed_at->toISOString() : null,
                'learning_duration_seconds' => (int) $details['progress']->learning_duration_seconds,
                'last_accessed_at' => $details['progress']->last_accessed_at ? $details['progress']->last_accessed_at->toISOString() : null,
                'current_second' => (int) $details['current_second'],
            ]
        ], 'Thao tác thành công');
    }

    /**
     * Check if the authenticated user has access to a specific lesson.
     *
     * @param int $id
     * @return JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function canAccessLesson(int $id): JsonResponse
    {
        $lesson = \App\Models\Lesson::find($id);
        if (!$lesson) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $course = $lesson->course;
        if (!$course) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        if ($lesson->status !== 'published' || $course->status !== 'published') {
            throw new \App\Exceptions\BusinessException('Nội dung chưa khả dụng.', 403);
        }

        $user = request()->user();
        
        if ($lesson->is_preview) {
            return ApiResponse::success([
                'can_access' => true,
            ], 'Thao tác thành công');
        }

        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $hasAccess = $user->can('canAccessLesson', $lesson);

        if (!$hasAccess) {
            throw new \App\Exceptions\BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        return ApiResponse::success([
            'can_access' => true,
        ], 'Thao tác thành công');
    }
}
