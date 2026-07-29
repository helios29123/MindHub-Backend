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
        $instructorId = (int) $request->user()->id;
        $summary = $this->withdrawalService->summary($instructorId);
        return response()->json([
            'success' => true,
            'message' => 'Lấy tổng quan rút tiền thành công.',
            'data' => (new InstructorWithdrawalSummaryResource($summary))->resolve($request),
        ]);
    }
    public function index(InstructorWithdrawalIndexRequest $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $withdrawals = $this->withdrawalService->paginate(
            $instructorId,
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
        $instructorId = (int) $request->user()->id;
        $withdrawal = $this->withdrawalService->show($instructorId, $id);
        if (!$withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu rút tiền.',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết yêu cầu rút tiền thành công.',
            'data' => (new InstructorWithdrawalDetailResource($withdrawal))->resolve($request),
        ]);
    }
    public function store(InstructorWithdrawalStoreRequest $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $withdrawal = $this->withdrawalService->store(
            $instructorId,
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
        $instructorId = (int) $request->user()->id;
        $accounts = $this->withdrawalService->payoutAccounts($instructorId);
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách tài khoản nhận tiền thành công.',
            'data' => $accounts ? (new InstructorPayoutAccountResource((object) $accounts))->resolve($request) : null,
        ]);
    }
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $instructorId = (int) $request->user()->id;
            $success = $this->withdrawalService->cancel($instructorId, $id);
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu rút tiền.',
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Hủy yêu cầu rút tiền thành công.',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }
    }
}