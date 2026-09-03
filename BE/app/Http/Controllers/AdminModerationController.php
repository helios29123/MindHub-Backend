<?php
namespace App\Http\Controllers;
use App\Http\Requests\Moderation\ApproveCourseRequest;
use App\Http\Requests\Moderation\ModerateItemRequest;
use App\Http\Requests\Moderation\PendingCourseQueryRequest;
use App\Http\Requests\Moderation\RejectcourseRequest;
use App\Http\Resources\ApiResource;
use App\Http\Resources\Moderation\CourseApprovalResource;
use App\Http\Resources\Moderation\CourseRejectResource;
use App\Http\Resources\Moderation\PendingCourseResource;
use App\Services\Moderation\CourseModerationService;
use App\Services\Moderation\ModerationService;
use App\Support\ApiResponse;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
class AdminModerationController extends Controller
{
    public function __construct(
        private readonly ModerationService $moderationService,
        private readonly CourseModerationService $courseModerationService
    ) {
    }
    public function courseReviews(PendingCourseQueryRequest $request): JsonResponse
    {
        $courses = $this->courseModerationService->getCourseReviews($request->validated());
        
        $pendingCount = \App\Models\Course::where('status', 'pending_review')->count();
        
        $todayStart = now()->startOfDay()->toDateTimeString();
        
        $approvedToday = \App\Models\Course::whereIn('status', ['published', 'approved'])
            ->where('updated_at', '>=', $todayStart)
            ->count();
            
        $rejectedToday = \App\Models\Course::where('status', 'rejected')
            ->where('updated_at', '>=', $todayStart)
            ->count();
        $totalCount = \App\Models\Course::whereIn('status', ['pending_review', 'approved', 'published', 'rejected'])->count();

        return ApiResponse::success(
            data: [
                'summary' => [
                    'total_count' => $totalCount,
                    'pending_count' => $pendingCount,
                    'approved_today' => $approvedToday,
                    'rejected_today' => $rejectedToday,
                ],
                'items' => PendingCourseResource::collection($courses)->resolve(request())
            ],
            message: 'Lấy dữ liệu thành công',
            status: 200,
            meta: \App\Support\PaginationMeta::fromPaginator($courses)
        );
    }
    public function approveCourse(ApproveCourseRequest $request, int $courseId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $course = $this->courseModerationService->approveCourse(
                (int) $validated['id'],
                (int) $request->user()->id,
            );
            return ApiResponse::success(
                new CourseApprovalResource($course),
                'Thao tác thành công',
                200
            );
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Không tìm thấy dữ liệu.', [], 404);
        } catch (DomainException $exception) {
            return ApiResponse::error(
                $exception->getMessage() ?: 'Trạng thái khóa học không hợp lệ để xử lý.',
                [],
                400
            );
        }
    }
    public function rejectCourse(RejectcourseRequest $request, int $courseId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $course = $this->courseModerationService->rejectCourse(
                (int) $validated['id'],
                (string) $validated['admin_reject_reason'],
                (int) $request->user()->id,
            );
            return ApiResponse::success(
                new CourseRejectResource($course),
                'Thao tác thành công',
                200
            );
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Không tìm thấy dữ liệu.', [], 404);
        } catch (DomainException $exception) {
            return ApiResponse::error(
                $exception->getMessage() ?: 'Trạng thái khóa học không hợp lệ để xử lý.',
                [],
                400
            );
        }
    }
    public function moderateItem(ModerateItemRequest $request, mixed $id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return ApiResponse::error('Dữ liệu không hợp lệ.', $validator->errors()->toArray(), 422);
        }
        $item = $this->moderationService->moderateItem((int) $id, $request->validated());
        return ApiResponse::success(
            new ApiResource($item),
            'Thao tác thành công',
            200
        );
    }

    public function moderationItems(\Illuminate\Http\Request $request): JsonResponse
    {
        $res = $this->moderationService->getModerationItems($request->all());
        return ApiResponse::success(
            data: [
                'summary' => $res['summary'],
                'items' => $res['items'],
            ],
            message: 'Thao tác thành công',
            status: 200,
            meta: $res['meta']
        );
    }

    public function moderationItemDetail(\Illuminate\Http\Request $request, string $targetType, mixed $id): JsonResponse
    {
        try {
            $item = $this->moderationService->getModerationItemDetail($targetType, (int) $id);
            return ApiResponse::success(
                data: $item,
                message: 'Thao tác thành công',
                status: 200
            );
        } catch (ModelNotFoundException|\App\Exceptions\BusinessException $e) {
            return ApiResponse::error($e->getMessage() ?: 'Không tìm thấy dữ liệu.', [], 404);
        }
    }

    public function aiSuggestCategory(\Illuminate\Http\Request $request, int $courseId, \App\Services\AI\DeepSeekService $deepSeekService): JsonResponse
    {
        $course = \App\Models\Course::findOrFail($courseId);
        $suggestion = $deepSeekService->suggestCategoryForCourse($course);

        return ApiResponse::success(
            $suggestion,
            'AI phân tích và gợi ý danh mục thành công.'
        );
    }

    public function aiApplyCategory(\Illuminate\Http\Request $request, int $courseId): JsonResponse
    {
        $course = \App\Models\Course::findOrFail($courseId);
        $type = $request->input('type', 'existing');

        if ($type === 'create_new') {
            $name = trim((string) $request->input('name'));
            $slug = trim((string) $request->input('slug')) ?: \Illuminate\Support\Str::slug($name);
            $description = (string) $request->input('description', '');

            if (empty($name)) {
                return ApiResponse::error('Tên danh mục không được để trống.', [], 422);
            }

            $category = \App\Models\Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'status' => 'active',
                ]
            );

            $course->categories()->sync([$category->id]);

            return ApiResponse::success([
                'course_id' => $course->id,
                'category_id' => $category->id,
                'category_name' => $category->name,
                'created_new' => true,
            ], 'Tạo mới và gán danh mục thành công.');
        } else {
            $categoryId = (int) $request->input('category_id');
            $category = \App\Models\Category::findOrFail($categoryId);

            $course->categories()->sync([$category->id]);

            return ApiResponse::success([
                'course_id' => $course->id,
                'category_id' => $category->id,
                'category_name' => $category->name,
                'created_new' => false,
            ], 'Gán danh mục cho khóa học thành công.');
        }
    }
}