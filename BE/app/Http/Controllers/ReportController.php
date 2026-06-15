<?php
namespace App\Http\Controllers;
use App\Http\Requests\Report\CompletionRateQueryRequest;
use App\Http\Resources\Report\CompletionRateResource;
use App\Services\Report\InstructorReportService;
use App\Http\Requests\Report\TopCourseReportRequest;
use App\Http\Resources\Report\TopCourseReportResource;
use App\Http\Requests\Report\TopInstructorReportRequest;
use App\Http\Resources\Report\TopInstructorReportResource;
use App\Services\Report\ReportService;
use App\Support\ApiResponse;

final class ReportController extends Controller
{
    public function __construct(
        private readonly InstructorReportService $reportService
    ) {}
    public function completionRate(CompletionRateQueryRequest $request)
    {
        $paginator = $this->reportService->getCompletionRate($request->validated(), $request->user());
        return response()->json([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'summary' => [
                    'total_courses' => $paginator->total(),
                ],
                'items' => CompletionRateResource::collection($paginator),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ]
            ]
        ]);
    }

    public function topCourses(TopCourseReportRequest $request, ReportService $adminReportService)
    {
        $result = $adminReportService->getTopCoursesReport($request->validated());
        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'summary' => $result['summary'],
                'items' => TopCourseReportResource::collection($paginator),
            ],
            message: 'Lấy báo cáo khóa học bán chạy thành công',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function topInstructors(TopInstructorReportRequest $request, ReportService $adminReportService)
    {
        $result = $adminReportService->getTopInstructorsReport($request->validated());
        $paginator = $result['paginator'];

        return ApiResponse::success(
            data: [
                'items' => TopInstructorReportResource::collection($paginator),
            ],
            message: 'Lấy báo cáo giảng viên thành công',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }
}