<?php
namespace App\Http\Controllers;
use App\Http\Requests\Instructor\InstructorPayoutAccountIndexRequest;
use App\Http\Requests\Instructor\InstructorWithdrawalIndexRequest;
use App\Http\Requests\Instructor\InstructorWithdrawalStoreRequest;
use App\Http\Resources\Instructor\InstructorPayoutAccountResource;
use App\Http\Resources\Instructor\InstructorWithdrawalDetailResource;
use App\Http\Resources\Instructor\InstructorWithdrawalResource;
use App\Http\Resources\Instructor\InstructorWithdrawalSummaryResource;
use App\Services\Instructor\InstructorWithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
final class InstructorWithdrawalController extends Controller
{
    public function __construct(
        private readonly InstructorWithdrawalService $withdrawalService
    ) {
    }
    public function summary(Request $request): JsonResponse
    {
        $summary = $this->withdrawalService->summary($request->user());
        return response()->json([
            'success' => true,
            'message' => 'Lấy tổng quan rút tiền thành công.',
            'data' => (new InstructorWithdrawalSummaryResource($summary))->resolve($request),
        ]);
    }
    public function index(InstructorWithdrawalIndexRequest $request): JsonResponse
    {
        $withdrawals = $this->withdrawalService->paginate(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Lấy lịch sử rút tiền thành công.',
            'data' => InstructorWithdrawalResource::collection($withdrawals->items())->resolve($request),
            'meta' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total(),
            ],
        ]);
    }
    public function show(Request $request, int $id): JsonResponse
    {
        $withdrawal = $this->withdrawalService->show($request->user(), $id);
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết yêu cầu rút tiền thành công.',
            'data' => (new InstructorWithdrawalDetailResource($withdrawal))->resolve($request),
        ]);
    }
    public function store(InstructorWithdrawalStoreRequest $request): JsonResponse
    {
        $withdrawal = $this->withdrawalService->store(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Tạo yêu cầu rút tiền thành công.',
            'data' => (new InstructorWithdrawalResource($withdrawal))->resolve($request),
        ], 201);
    }
    public function payoutAccounts(InstructorPayoutAccountIndexRequest $request): JsonResponse
    {
        $accounts = $this->withdrawalService->payoutAccounts(
            $request->user(),
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách tài khoản nhận tiền thành công.',
            'data' => InstructorPayoutAccountResource::collection($accounts)->resolve($request),
        ]);
    }
}