<?php

namespace App\Http\Controllers;

use App\Http\Requests\Learning\MyCoursesRequest;
use App\Http\Resources\Learning\MyCourseResource;
use App\Services\Learning\LearningService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Resources\Learning\LearningLessonResource;
use App\Http\Requests\Learning\CourseOutlineRequest;
use App\Http\Resources\Learning\LearningOutlineSectionResource;
use App\Http\Requests\Learning\SaveVideoProgressRequest;
use App\Http\Requests\Learning\CompleteLessonRequest;
use App\Http\Requests\Learning\CourseProgressRequest;
use App\Http\Requests\Learning\LearningLogsRequest;
use App\Http\Resources\Learning\LearningLogResource;
use App\Http\Requests\Learning\DownloadAssetRequest;
use App\Http\Resources\Learning\AssetDownloadResource;
use App\Http\Requests\Learning\NextLessonRequest;

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

    /**
     * Get the outline of a purchased course.
     *
     * @param CourseOutlineRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function outline(CourseOutlineRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $result = $this->learningService->getCourseOutline($user, $id);

        $resource = LearningOutlineSectionResource::collection($result['sections']);
        $resource->collection->each(function ($secResource) use ($result) {
            $secResource->additional(['progresses' => $result['progresses']]);
        });

        return ApiResponse::success($resource, 'Lấy lộ trình khóa học thành công');
    }

    /**
     * Save/update learning progress for a video lesson.
     *
     * @param SaveVideoProgressRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function saveVideoProgress(SaveVideoProgressRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $details = $this->learningService->saveVideoProgress($user, $id, $request->validated());

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
     * Get details of the most recently accessed lesson or the first lesson of the latest purchased course to resume learning.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function resume(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user();
        
        $details = $this->learningService->resumeLearning($user);

        return ApiResponse::success([
            'course' => [
                'id' => $details['course']->id,
                'title' => $details['course']->title,
                'slug' => $details['course']->slug,
            ],
            'lesson' => new LearningLessonResource($details['lesson']),
            'progress' => $details['progress'] ? [
                'status' => $details['progress']->status,
                'started_at' => $details['progress']->started_at ? $details['progress']->started_at->toISOString() : null,
                'completed_at' => $details['progress']->completed_at ? $details['progress']->completed_at->toISOString() : null,
                'learning_duration_seconds' => (int) $details['progress']->learning_duration_seconds,
                'last_accessed_at' => $details['progress']->last_accessed_at ? $details['progress']->last_accessed_at->toISOString() : null,
                'current_second' => (int) $details['current_second'],
            ] : null,
            'current_second' => (int) $details['current_second'],
        ], 'Thao tác thành công');
    }

    /**
     * Mark a lesson as completed.
     *
     * @param CompleteLessonRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function completeLesson(CompleteLessonRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $details = $this->learningService->completeLesson($user, $id, $request->validated());

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
     * Get learning progress percentage for a course.
     *
     * @param CourseProgressRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function courseProgress(CourseProgressRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $progress = $this->learningService->getCourseProgress($user, $id);

        return ApiResponse::success($progress, 'Thao tác thành công');
    }

    /**
     * Get learning logs (timeline) for the authenticated learner.
     *
     * @param LearningLogsRequest $request
     * @return JsonResponse
     */
    public function learningLogs(LearningLogsRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $logs = $this->learningService->getLearningLogs(
            $user,
            $request->validated()
        );

        return ApiResponse::paginated(
            LearningLogResource::collection($logs),
            $logs,
            'Thao tác thành công'
        );
    }

    /**
     * Get details of a lesson asset for download.
     *
     * @param DownloadAssetRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function downloadAsset(DownloadAssetRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $asset = $this->learningService->downloadAsset($user, $id);

        return ApiResponse::success(
            new AssetDownloadResource($asset),
            'Thao tác thành công'
        );
    }

    /**
     * Suggest the next lesson in the course structure.
     *
     * @param NextLessonRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function nextLesson(NextLessonRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $nextLesson = $this->learningService->nextLesson($user, $id);

        return ApiResponse::success(
            $nextLesson ? new LearningLessonResource($nextLesson) : null,
            'Thao tác thành công'
        );
    }

    /**
     * Get rule-based course recommendations for the authenticated learner.
     *
     * @param \App\Http\Requests\Learning\RuleBasedRecommendationRequest $request
     * @param \App\Services\Learning\RuleBasedRecommendationService $service
     * @return JsonResponse
     */
    public function ruleBasedRecommendations(\App\Http\Requests\Learning\RuleBasedRecommendationRequest $request, \App\Services\Learning\RuleBasedRecommendationService $service): JsonResponse
    {
        $learnerId = $request->user()->id;
        $filters = $request->validated();
        
        $result = $service->getRuleBasedRecommendations($learnerId, $filters);
        
        if ($result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            if ($result->isEmpty()) {
                return ApiResponse::success([], 'Chﾆｰa ﾄ黛ｻｧ d盻ｯ li盻㎡ cﾃ｡ nhﾃ｢n hﾃｳa, tr蘯｣ g盻｣i ﾃｽ ph盻・bi蘯ｿn.');
            }
            return ApiResponse::paginated(
                \App\Http\Resources\Learning\RuleBasedRecommendationResource::collection($result),
                $result,
                'Lấy gợi ý khóa học thành công.'
            );
        }
        
        if (empty($result)) {
            return ApiResponse::success([], 'Chﾆｰa ﾄ黛ｻｧ d盻ｯ li盻㎡ cﾃ｡ nhﾃ｢n hﾃｳa, tr蘯｣ g盻｣i ﾃｽ ph盻・bi蘯ｿn.');
        }
        
        return ApiResponse::success(
            \App\Http\Resources\Learning\RuleBasedRecommendationResource::collection($result),
            'Lấy gợi ý khóa học thành công.'
        );
    }

    /**
     * Get next learning path recommendations for the authenticated learner.
     *
     * @param \App\Http\Requests\Learning\NextLearningPathRequest $request
     * @param \App\Services\Learning\NextLearningPathService $service
     * @return JsonResponse
     */
    public function nextLearningPath(\App\Http\Requests\Learning\NextLearningPathRequest $request, \App\Services\Learning\NextLearningPathService $service): JsonResponse
    {
        $learnerId = $request->user()->id;
        $filters = $request->validated();
        
        $result = $service->getNextLearningPath($learnerId, $filters);
        
        if (empty($result)) {
            return ApiResponse::success([], 'Chﾆｰa cﾃｳ ﾄ黛ｻｧ d盻ｯ li盻㎡ ﾄ黛ｻ・g盻｣i ﾃｽ l盻・trﾃｬnh.');
        }
        
        return ApiResponse::success(
            \App\Http\Resources\Learning\NextLearningPathResource::collection($result),
            'Lấy gợi ý lộ trình học tập thành công.'
        );
    }

    /**
     * Get dynamic alerts for the authenticated learner.
     *
     * @param \App\Http\Requests\Learning\DynamicAlertRequest $request
     * @param \App\Services\Learning\DynamicAlertService $service
     * @return JsonResponse
     */
    public function dynamicAlerts(\App\Http\Requests\Learning\DynamicAlertRequest $request, \App\Services\Learning\DynamicAlertService $service): JsonResponse
    {
        $learnerId = $request->user()->id;
        $filters = $request->validated();
        
        $result = $service->getDynamicAlerts($learnerId, $filters);
        
        if (empty($result)) {
            return ApiResponse::success([], 'Khﾃｴng cﾃｳ c蘯｣nh bﾃ｡o m盻嬖.');
        }
        
        return ApiResponse::success(
            \App\Http\Resources\Learning\DynamicAlertResource::collection($result),
            'Lấy cảnh báo thành công.'
        );
    }

    /**
     * Generate a signed/temporary URL for a lesson asset.
     *
     * @param \App\Http\Requests\Learning\SignedAssetUrlRequest $request
     * @param int $assetId
     * @param \App\Services\Learning\SignedAssetUrlService $service
     * @return JsonResponse
     */
    public function signedAssetUrl(\App\Http\Requests\Learning\SignedAssetUrlRequest $request, mixed $assetId, \App\Services\Learning\SignedAssetUrlService $service): JsonResponse
    {
        $learnerId = $request->user()->id;
        $validated = $request->validated();
        $ttlSeconds = $validated['ttl_seconds'] ?? null;
        
        $result = $service->generateSignedAssetUrl($learnerId, (int) $assetId, $ttlSeconds);
        
        return ApiResponse::success(
            new \App\Http\Resources\Learning\SignedAssetUrlResource($result),
            'Lấy URL bảo mật thành công.'
        );
    }
    public function dashboard(\App\Http\Requests\Learning\LearningDashboardRequest $request, \App\Services\Learning\LearningDashboardService $service): \Illuminate\Http\JsonResponse
    {
        $dashboard = $service->getDashboard(
            (int) $request->user()->id,
            $request->validated()
        );

        $message = ((int) ($dashboard['total_courses'] ?? 0)) === 0
            ? 'Chưa có dữ liệu học tập.'
            : 'Lấy dashboard học tập thành công.';

        return \App\Support\ApiResponse::success(
            new \App\Http\Resources\Learning\LearningDashboardResource($dashboard),
            $message
        );
    }

    public function activityCalendar(\Illuminate\Http\Request $request, \App\Services\Learning\LearningDashboardService $service): \Illuminate\Http\JsonResponse
    {
        $month = (int) $request->query('month', (int) date('m'));
        $year = (int) $request->query('year', (int) date('Y'));
        
        $calendar = $service->getActivityCalendar(
            (int) $request->user()->id,
            $month,
            $year
        );

        return \App\Support\ApiResponse::success(
            $calendar,
            'Lấy thông tin lịch hoạt động thành công.'
        );
    }

    /**
     * Get watermark info for a video lesson.
     *
     * @param \App\Http\Requests\Learning\WatermarkInfoRequest $request
     * @param mixed $lessonId
     * @param \App\Services\Learning\WatermarkInfoService $service
     * @return JsonResponse
     */
    public function watermarkInfo(\App\Http\Requests\Learning\WatermarkInfoRequest $request, mixed $lessonId, \App\Services\Learning\WatermarkInfoService $service): JsonResponse
    {
        $learnerId = $request->user()->id;
        $filters = $request->validated();
        
        $result = $service->getWatermarkInfo($learnerId, (int) $lessonId, $filters);
        
        return ApiResponse::success(
            new \App\Http\Resources\Learning\WatermarkInfoResource($result),
            'Lấy thông tin watermark thành công.'
        );
    }
    /**
     * Generate a signed temporary stream URL for a private lesson video.
     */
    public function signedLessonVideoUrl(
        \App\Http\Requests\Learning\SignedLessonVideoUrlRequest $request,
        mixed $id,
        \App\Services\Learning\LessonVideoAccessService $service
    ): \Illuminate\Http\JsonResponse {
        $validated = $request->validated();
        $result = $service->generateSignedStreamUrl(
            (int) $request->user()->id,
            (int) $id,
            $validated['ttl_seconds'] ?? null,
            $request
        );
        return \App\Support\ApiResponse::success(
            new \App\Http\Resources\Learning\SignedLessonVideoUrlResource($result),
            'Tạo link xem video thành công.'
        );
    }
    /**
     * Stream a private lesson video after validating signed URL and enrollment.
     */
    public function streamLessonVideo(
        \App\Http\Requests\Learning\StreamLessonVideoRequest $request,
        mixed $id,
        \App\Services\Learning\LessonVideoAccessService $service
    ): \Symfony\Component\HttpFoundation\StreamedResponse {
        // Route stream không qua auth.session -> user() thường null.
        // learnerId lấy từ tham số 'u' (đã nằm trong chữ ký URL, không thể giả mạo);
        // service.streamVideo vẫn kiểm tra chữ ký + enrollment của learnerId này.
        $learnerId = (int) ($request->user()?->id ?? $request->query('u', 0));
        return $service->streamVideo(
            $learnerId,
            (int) $id,
            $request
        );
    }

    public function getLessonNotes(Request $request, mixed $id): JsonResponse
    {
        $notes = $this->learningService->getLessonNotes((int) $id, $request->user());
        return ApiResponse::success($notes, 'Lấy danh sách ghi chú thành công');
    }

    public function createLessonNote(Request $request, mixed $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'note_time_second' => 'nullable|integer|min:0',
        ]);
        $note = $this->learningService->createLessonNote((int) $id, $validated, $request->user());
        return ApiResponse::success($note, 'Tạo ghi chú thành công', 201);
    }

    public function updateLessonNote(Request $request, mixed $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'sometimes|required|string|max:1000',
            'note_time_second' => 'nullable|integer|min:0',
        ]);
        $note = $this->learningService->updateLessonNote((int) $id, $validated, $request->user());
        return ApiResponse::success($note, 'Cập nhật ghi chú thành công');
    }

    public function deleteLessonNote(Request $request, mixed $id): JsonResponse
    {
        $this->learningService->deleteLessonNote((int) $id, $request->user());
        return ApiResponse::success(null, 'Xóa ghi chú thành công');
    }

    public function streak(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Vui lòng đăng nhập để xem thông tin chuỗi học tập.', 401);
        }

        $streakData = $this->learningService->getLearningStreak($user);

        return ApiResponse::success(
            $streakData,
            'Lấy thông tin chuỗi học tập thành công.'
        );
    }
}
