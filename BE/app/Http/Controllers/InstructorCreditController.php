<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\StoreInstructorCreditOrderRequest;
use App\Models\CourseCreditPackage;
use App\Models\InstructorCreditTransaction;
use App\Services\Instructor\CourseCreditService;
use App\Services\Instructor\InstructorCreditOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorCreditController extends Controller
{
    public function __construct(
        private readonly CourseCreditService $courseCreditService,
        private readonly InstructorCreditOrderService $instructorCreditOrderService
    ) {
    }

    public function packages(): JsonResponse
    {
        $packages = CourseCreditPackage::query()
            ->where('status', CourseCreditPackage::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    public function balance(Request $request): JsonResponse
    {
        $balance = $this->courseCreditService->getBalanceForDisplay((int) $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $balance,
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = InstructorCreditTransaction::query()
            ->where('instructor_id', (int) $request->user()->id)
            ->latest('id')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function createOrder(StoreInstructorCreditOrderRequest $request): JsonResponse
    {
        $order = $this->instructorCreditOrderService->createOrder(
            (int) $request->user()->id,
            (int) $request->input('credit_package_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn mua gói lượt thành công.',
            'data' => $order,
        ], 201);
    }
}
