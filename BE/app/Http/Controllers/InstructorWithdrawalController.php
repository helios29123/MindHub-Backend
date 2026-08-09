<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\Instructor\InstructorPayoutAccountIndexRequest;
use App\Http\Requests\Instructor\InstructorWithdrawalIndexRequest;
use App\Http\Requests\Instructor\InstructorWithdrawalStoreRequest;
use App\Http\Resources\Instructor\InstructorPayoutAccountResource;
use App\Http\Resources\Instructor\InstructorWithdrawalDetailResource;
use App\Http\Resources\Instructor\InstructorWithdrawalResource;
use App\Http\Resources\Instructor\InstructorWithdrawalSummaryResource;
use App\Services\Instructor\InstructorWithdrawalService;
use App\Services\Payout\EarlyWithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InstructorWithdrawalController extends Controller
{
    public function __construct(
        private readonly InstructorWithdrawalService $withdrawalService,
        private readonly EarlyWithdrawalService $earlyWithdrawalService
    ) {
    }

    public function summary(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $summary = $this->earlyWithdrawalService->getPaymentSummary($instructorId);

        return response()->json([
            'success' => true,
            'message' => 'Lấy tổng quan thanh toán thành công.',
            'data' => (new InstructorWithdrawalSummaryResource($summary))->resolve($request),
        ]);
    }

    public function requestEarlyWithdrawalOtp(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:200000'],
            'payout_account_id' => ['nullable', 'integer'],
        ]);

        $instructorId = (int) $request->user()->id;
        $amount = (float) $request->input('amount');
        $payoutAccountId = $request->input('payout_account_id') ? (int) $request->input('payout_account_id') : null;

        try {
            $otpData = $this->earlyWithdrawalService->requestOtp($instructorId, $amount, $payoutAccountId);

            return response()->json([
                'success' => true,
                'message' => 'Mã OTP đã được gửi đến email của bạn.',
                'data' => $otpData,
            ]);
        } catch (BusinessException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    public function createEarlyWithdrawal(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:200000'],
            'payout_account_id' => ['nullable', 'integer'],
            'otp' => ['nullable', 'string'],
        ]);

        $instructorId = (int) $request->user()->id;
        $amount = (float) $request->input('amount');
        $payoutAccountId = $request->input('payout_account_id') ? (int) $request->input('payout_account_id') : null;
        $otpCode = $request->input('otp') ? (string) $request->input('otp') : null;

        try {
            $withdrawal = $this->earlyWithdrawalService->createEarlyWithdrawal($instructorId, $amount, $payoutAccountId, $otpCode);

            return response()->json([
                'success' => true,
                'message' => 'Tạo yêu cầu thanh toán sớm thành công.',
                'data' => (new InstructorWithdrawalResource($withdrawal))->resolve($request),
            ], 201);
        } catch (BusinessException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
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
            'message' => 'Lấy lịch sử thanh toán thành công.',
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

        if (! $withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin đợt thanh toán.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết đợt thanh toán thành công.',
            'data' => (new InstructorWithdrawalDetailResource($withdrawal))->resolve($request),
        ]);
    }

    public function store(InstructorWithdrawalStoreRequest $request): JsonResponse
    {
        return $this->createEarlyWithdrawal($request);
    }

    public function payoutAccounts(InstructorPayoutAccountIndexRequest $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $accounts = $this->withdrawalService->payoutAccounts($instructorId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách tài khoản nhận tiền thành công.',
            'data' => InstructorPayoutAccountResource::collection($accounts)->resolve($request),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $instructorId = (int) $request->user()->id;
            $success = $this->earlyWithdrawalService->cancelEarlyWithdrawal($instructorId, $id);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu thanh toán sớm.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Hủy yêu cầu thanh toán sớm thành công.',
            ]);
        } catch (BusinessException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $exception->getCode() ?: 422);
        }
    }
}