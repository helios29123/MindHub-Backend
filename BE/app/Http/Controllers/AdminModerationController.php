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
    public function pendingCourses(PendingCourseQueryRequest $request): JsonResponse
    {
        $courses = $this->courseModerationService->getPendingCourses($request->validated());
        return ApiResponse::success(
            PendingCourseResource::collection($courses),
            'Lấy dữ liệu thành công',
            200,
            [
                'page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ]
        );
    }
    public function approveCourse(ApproveCourseRequest $request, mixed $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $course = $this->courseModerationService->approveCourse(
                (int) $validated['id']
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
    public function rejectCourse(RejectcourseRequest $request, mixed $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $course = $this->courseModerationService->rejectCourse(
                (int) $validated['id'],
                (string) $validated['admin_reject_reason']
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
}