<?php

namespace App\Http\Controllers;

use App\Http\Requests\Learning\MyCoursesRequest;
use App\Http\Resources\Learning\MyCourseResource;
use App\Services\Learning\LearningService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

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
            'L陂ｯ・･y danh s・・ｽ｡ch kh・・ｽｳa h逶ｻ逧・・・ｦ･・｣ mua th・・｣ｰnh c・・ｽｴng.'
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
        ], 'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng');
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
            throw new \App\Exceptions\BusinessException('Kh・・ｽｴng t・・ｽｬm th陂ｯ・･y d逶ｻ・ｯ li逶ｻ緕｡.', 404);
        }

        $course = $lesson->course;
        if (!$course) {
            throw new \App\Exceptions\BusinessException('Kh・・ｽｴng t・・ｽｬm th陂ｯ・･y d逶ｻ・ｯ li逶ｻ緕｡.', 404);
        }

        if ($lesson->status !== 'published' || $course->status !== 'published') {
            throw new \App\Exceptions\BusinessException('N逶ｻ蜀・dung ch・・ｽｰa kh陂ｯ・｣ d逶ｻ・･ng.', 403);
        }

        $user = request()->user();
        
        if ($lesson->is_preview) {
            return ApiResponse::success([
                'can_access' => true,
            ], 'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng');
        }

        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $hasAccess = $user->can('canAccessLesson', $lesson);

        if (!$hasAccess) {
            throw new \App\Exceptions\BusinessException('B陂ｯ・｡n ch・・ｽｰa c・・ｽｳ quy逶ｻ・ｽ truy c陂ｯ・ｭp n逶ｻ蜀・dung n・・｣ｰy.', 403);
        }

        return ApiResponse::success([
            'can_access' => true,
        ], 'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng');
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

        return ApiResponse::success($resource, 'L陂ｯ・･y l逶ｻ繝ｻtr・・ｽｬnh kh・・ｽｳa h逶ｻ逧・th・・｣ｰnh c・・ｽｴng');
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
        ], 'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng');
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
        ], 'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng');
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
        ], 'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng');
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

        return ApiResponse::success($progress, 'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng');
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
            'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng'
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
            'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng'
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
            'Thao t・・ｽ｡c th・・｣ｰnh c・・ｽｴng'
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
                return ApiResponse::success([], 'Ch・・ｽｰa ・・ｻ幢ｽｻ・ｧ d逶ｻ・ｯ li逶ｻ緕｡ c・・ｽ｡ nh・・ｽ｢n h・・ｽｳa, tr陂ｯ・｣ g逶ｻ・｣i ・・ｽｽ ph逶ｻ繝ｻbi陂ｯ・ｿn.');
            }
            return ApiResponse::paginated(
                \App\Http\Resources\Learning\RuleBasedRecommendationResource::collection($result),
                $result,
                'L陂ｯ・･y g逶ｻ・｣i ・・ｽｽ kh・・ｽｳa h逶ｻ逧・th・・｣ｰnh c・・ｽｴng.'
            );
        }
        
        if (empty($result)) {
            return ApiResponse::success([], 'Ch・・ｽｰa ・・ｻ幢ｽｻ・ｧ d逶ｻ・ｯ li逶ｻ緕｡ c・・ｽ｡ nh・・ｽ｢n h・・ｽｳa, tr陂ｯ・｣ g逶ｻ・｣i ・・ｽｽ ph逶ｻ繝ｻbi陂ｯ・ｿn.');
        }
        
        return ApiResponse::success(
            \App\Http\Resources\Learning\RuleBasedRecommendationResource::collection($result),
            'L陂ｯ・･y g逶ｻ・｣i ・・ｽｽ kh・・ｽｳa h逶ｻ逧・th・・｣ｰnh c・・ｽｴng.'
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
            return ApiResponse::success([], 'Ch・・ｽｰa c・・ｽｳ ・・ｻ幢ｽｻ・ｧ d逶ｻ・ｯ li逶ｻ緕｡ ・・ｻ幢ｽｻ繝ｻg逶ｻ・｣i ・・ｽｽ l逶ｻ繝ｻtr・・ｽｬnh.');
        }
        
        return ApiResponse::success(
            \App\Http\Resources\Learning\NextLearningPathResource::collection($result),
            'L陂ｯ・･y g逶ｻ・｣i ・・ｽｽ l逶ｻ繝ｻtr・・ｽｬnh h逶ｻ逧・t陂ｯ・ｭp th・・｣ｰnh c・・ｽｴng.'
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
            return ApiResponse::success([], 'Kh・・ｽｴng c・・ｽｳ c陂ｯ・｣nh b・・ｽ｡o m逶ｻ螫・');
        }
        
        return ApiResponse::success(
            \App\Http\Resources\Learning\DynamicAlertResource::collection($result),
            'L陂ｯ・･y c陂ｯ・｣nh b・・ｽ｡o th・・｣ｰnh c・・ｽｴng.'
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
            'L陂ｯ・･y URL b陂ｯ・｣o m陂ｯ・ｭt th・・｣ｰnh c・・ｽｴng.'
        );
    }
    public function dashboard(\App\Http\Requests\Learning\LearningDashboardRequest $request, \App\Services\Learning\LearningDashboardService $service): \Illuminate\Http\JsonResponse
    {
        $dashboard = $service->getDashboard(
            (int) $request->user()->id,
            $request->validated()
        );

        $message = ((int) ($dashboard['total_courses'] ?? 0)) === 0
            ? 'Chﾆｰa cﾃｳ d盻ｯ li盻㎡ h盻皇 t蘯ｭp.'
            : 'L蘯･y dashboard h盻皇 t蘯ｭp thﾃnh cﾃｴng.';

        return \App\Support\ApiResponse::success(
            new \App\Http\Resources\Learning\LearningDashboardResource($dashboard),
            $message
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
            'L蘯･y thﾃｴng tin watermark thﾃnh cﾃｴng.'
        );
    }
    public function courseOverview(\App\Http\Requests\Learning\CourseLearningOverviewRequest $request, mixed $courseId, \App\Services\Learning\CourseLearningOverviewService $service): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();

        $overview = $service->getOverview(
            (int) $request->user()->id,
            (int) $validated['course_id'],
            $validated
        );

        return \App\Support\ApiResponse::success(
            new \App\Http\Resources\Learning\CourseLearningOverviewResource($overview),
            'Lấy tổng quan khóa học thành công.'
        );
    }
}
