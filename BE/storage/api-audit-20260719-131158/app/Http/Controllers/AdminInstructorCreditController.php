<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AdjustInstructorCreditRequest;
use App\Models\InstructorCreditTransaction;
use App\Services\Instructor\CourseCreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminInstructorCreditController extends Controller
{
    public function __construct(
        private readonly CourseCreditService $courseCreditService
    ) {
    }

    public function show(int $instructorId): JsonResponse
    {
        $balance = $this->courseCreditService->getBalanceForDisplay($instructorId);

        return response()->json([
            'success' => true,
            'data' => $balance,
        ]);
    }

    public function transactions(Request $request, int $instructorId): JsonResponse
    {
        $transactions = InstructorCreditTransaction::query()
            ->where('instructor_id', $instructorId)
            ->latest('id')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function adjust(AdjustInstructorCreditRequest $request, int $instructorId): JsonResponse
    {
        $transaction = $this->courseCreditService->adjustCredits(
            $instructorId,
            (int) $request->input('credits'),
            $request->input('note')
        );

        return response()->json([
            'success' => true,
            'message' => 'Điều chỉnh lượt thành công.',
            'data' => $transaction,
        ]);
    }
}
