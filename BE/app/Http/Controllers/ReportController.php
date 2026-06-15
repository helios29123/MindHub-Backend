<?php
namespace App\Http\Controllers;
use App\Http\Requests\Report\CompletionRateQueryRequest;
use App\Http\Resources\Report\CompletionRateResource;
use App\Services\Report\InstructorReportService;
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
}