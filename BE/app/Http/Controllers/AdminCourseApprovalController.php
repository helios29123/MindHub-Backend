<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RejectCourseRequest;
use App\Services\Instructor\CourseCreditService;
use Illuminate\Http\JsonResponse;

class AdminCourseApprovalController extends Controller
{
    public function __construct(
        private readonly CourseCreditService $courseCreditService
    ) {
    }

    public function approve(int $courseId): JsonResponse
    {
        $course = $this->courseCreditService->approveCourseAndDeductCredit($courseId);

        return response()->json([
            'success' => true,
            'message' => 'Duyệt khóa học thành công và đã trừ 1 lượt của giảng viên.',
            'data' => $course,
        ]);
    }

    public function reject(RejectCourseRequest $request, int $courseId): JsonResponse
    {
        $course = $this->courseCreditService->rejectCourse(
            $courseId,
            $request->input('reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Từ chối khóa học thành công. Không trừ lượt của giảng viên.',
            'data' => $course,
        ]);
    }
}
