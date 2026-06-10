<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\Marketing\CourseAnnouncementRequest;
use App\Http\Resources\CourseAnnouncementResource;
use App\Models\Course;
use App\Services\MarketingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class MarketingController extends Controller
{
    public function __construct(
        private readonly MarketingService $marketingService
    ) {
    }

    public function courseAnnouncements(CourseAnnouncementRequest $request): JsonResponse
    {
        $courseId = (int) $request->validated()['course_id'];
        $course = Course::find($courseId);

        if (!$course) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        // Verify ownership
        if ((int) $course->instructor_id !== (int) $request->user()->id) {
            throw new BusinessException('Bạn không có quyền thực hiện thao tác này.', 403);
        }

        // Return HTTP 501 as required by specifications
        return response()->json([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => (new CourseAnnouncementResource(null))->resolve(),
        ], 501);
    }
}
