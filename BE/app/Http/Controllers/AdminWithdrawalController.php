<?php

namespace App\Http\Controllers;

use App\Models\Revenue;
use App\Models\WithdrawRequest;
use App\Services\Payout\EarlyWithdrawalService;
use App\Services\Payout\PayoutService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AdminWithdrawalController extends Controller
{
    public function __construct(
        private readonly EarlyWithdrawalService $earlyWithdrawalService,
        private readonly PayoutService $payoutService
    ) {
    }

    /**
     * GET /api/admin/withdrawals
     */
    public function index(Request $request): JsonResponse
    {
        $query = WithdrawRequest::query()->with('user');

        // Apply filters
        // 1. Search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('provider_payout_id', 'like', "%{$search}%")
                  ->orWhere('account_number_snapshot', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
                
                if (preg_match('/^W[DR]-(\d+)$/i', $search, $matches)) {
                    $q->orWhere('id', (int)$matches[1]);
                }
            });
        }

        // 2. Time preset / Custom dates
        $timePreset = $request->input('time_preset');
        if ($timePreset === 'today') {
            $query->whereDate('requested_at', Carbon::today());
        } elseif ($timePreset === 'last_7_days') {
            $query->where('requested_at', '>=', Carbon::now()->subDays(7));
        } elseif ($timePreset === 'last_30_days') {
            $query->where('requested_at', '>=', Carbon::now()->subDays(30));
        } elseif ($timePreset === 'last_3_months') {
            $query->where('requested_at', '>=', Carbon::now()->subMonths(3));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->input('date_to'));
        }

        // 3. Amount Range
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', (float) $request->input('amount_min'));
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', (float) $request->input('amount_max'));
        }

        // Clone query for summary calculation BEFORE status filter
        $summaryQuery = clone $query;

        // Apply status filter only to main query
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Calculate KPIs
        $summaryData = $summaryQuery->select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('status')
            ->get();

        $kpis = [
            'total_requests' => 0,
            'pending_count' => 0,
            'approved_count' => 0,
            'rejected_count' => 0,
            'paid_count' => 0,
            'pending_amount' => 0.0,
            'approved_amount' => 0.0,
            'rejected_amount' => 0.0,
            'paid_amount' => 0.0,
        ];

        foreach ($summaryData as $row) {
            $status = $row->status;
            $count = (int) $row->count;
            $amount = (float) $row->total_amount;

            $kpis['total_requests'] += $count;

            if ($status === 'pending') {
                $kpis['pending_count'] = $count;
                $kpis['pending_amount'] = $amount;
            } elseif ($status === 'approved') {
                $kpis['approved_count'] = $count;
                $kpis['approved_amount'] = $amount;
            } elseif ($status === 'rejected') {
                $kpis['rejected_count'] = $count;
                $kpis['rejected_amount'] = $amount;
            } elseif ($status === 'paid') {
                $kpis['paid_count'] = $count;
                $kpis['paid_amount'] = $amount;
            }
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'requested_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortOrder !== 'none') {
            if ($sortBy === 'user_name') {
                $query->join('users', 'users.id', '=', 'withdraw_requests.user_id')
                      ->select('withdraw_requests.*')
                      ->orderBy('users.full_name', $sortOrder);
            } elseif ($sortBy === 'withdrawal_code') {
                $query->orderBy('withdraw_requests.id', $sortOrder);
            } elseif ($sortBy === 'last_updated_at') {
                $query->orderBy('withdraw_requests.updated_at', $sortOrder);
            } else {
                $allowedSortFields = ['id', 'amount', 'requested_at', 'status', 'updated_at'];
                if (in_array($sortBy, $allowedSortFields, true)) {
                    $query->orderBy('withdraw_requests.' . $sortBy, $sortOrder);
                } else {
                    $query->orderBy('withdraw_requests.requested_at', 'desc');
                }
            }
        } else {
            $query->orderBy('withdraw_requests.requested_at', 'desc');
        }

        // Paginate results
        $perPage = (int) $request->input('per_page', 20);
        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'withdrawal_code' => 'WD-' . $item->id,
                'amount' => (float) $item->amount,
                'status' => $item->status,
                'requested_at' => $this->formatDate($item->requested_at),
                'paid_at' => $this->formatDate($item->paid_at),
                'approved_at' => $this->formatDate($item->approved_at),
                'rejected_at' => $item->status === WithdrawRequest::STATUS_REJECTED ? $this->formatDate($item->updated_at) : null,
                'provider_payout_id' => $item->provider_payout_id,
                'payout_provider' => $item->payout_provider,
                'payout_mode' => in_array($item->status, [WithdrawRequest::STATUS_PAID, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_MANUAL_REQUIRED, WithdrawRequest::STATUS_FAILED])
                    ? ($item->payout_provider === 'manual' ? 'manual' : 'auto')
                    : null,
                'payout_snapshot' => [
                    'payout_account_id' => $item->payout_account_id,
                    'account_name' => $item->account_name_snapshot,
                    'account_number' => $item->account_number_snapshot,
                    'account_number_masked' => $this->maskAccountNumber($item->account_number_snapshot),
                    'provider' => $item->bank_name_snapshot ?: 'Bank Transfer',
                    'status' => 'active'
                ],
                'user' => [
                    'id' => $item->user?->id,
                    'full_name' => $item->user?->full_name,
                    'email' => $item->user?->email,
                    'avatar_url' => null, // Placeholder or fetch actual user avatar if available
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách yêu cầu rút tiền thành công.',
            'data' => [
                'summary' => $kpis,
                'items' => $items,
            ],
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        ]);
    }

    /**
     * GET /api/admin/withdrawals/{id}
     */
    public function show(int $id): JsonResponse
    {
        $withdrawal = WithdrawRequest::with(['user', 'payoutAccount'])->find($id);

        if (! $withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu rút tiền trong hệ thống.',
            ], 404);
        }

        $instructorId = $withdrawal->user_id;
        $amount = (float) $withdrawal->amount;

        $balanceBefore = $withdrawal->available_balance_before !== null ? (float) $withdrawal->available_balance_before : null;
        $balanceAfter = $withdrawal->available_balance_after !== null ? (float) $withdrawal->available_balance_after : null;
        
        // For backwards compatibility or displaying holding amount
        $holdingBalance = in_array($withdrawal->status, ['pending', 'approved', 'processing', 'manual_required']) ? $amount : 0;

        // Fetch Allocations
        $allocations = [];
        
        $pivotRevenues = $withdrawal->allocatedRevenues()->with('course')->get();
        foreach ($pivotRevenues as $rev) {
            $allocations[] = [
                'revenue_id' => $rev->id,
                'order_id' => $rev->order_id,
                'course_title' => $rev->course?->title ?? 'Khóa học #' . $rev->course_id,
                'amount' => (float) $rev->pivot->allocated_amount,
                'earned_at' => $this->formatDate($rev->earned_at),
            ];
        }

        if (empty($allocations)) {
            $directRevenues = $withdrawal->revenues()->with('course')->get();
            foreach ($directRevenues as $rev) {
                $allocations[] = [
                    'revenue_id' => $rev->id,
                    'order_id' => $rev->order_id,
                    'course_title' => $rev->course?->title ?? 'Khóa học #' . $rev->course_id,
                    'amount' => (float) $rev->instructor_amount,
                    'earned_at' => $this->formatDate($rev->earned_at),
                ];
            }
        }

        // Build Timeline
        $timeline = [];
        if ($withdrawal->requested_at) {
            $timeline[] = [
                'timestamp' => $this->formatDate($withdrawal->requested_at),
                'title' => 'Gửi yêu cầu rút tiền',
                'description' => 'Tạo yêu cầu rút ' . number_format($amount, 0, ',', '.') . ' VNĐ',
                'status' => 'info',
            ];
        }
        if ($withdrawal->approved_at) {
            $timeline[] = [
                'timestamp' => $this->formatDate($withdrawal->approved_at),
                'title' => 'Đã phê duyệt',
                'description' => 'Admin đã phê duyệt yêu cầu rút tiền.',
                'status' => 'success',
            ];
        }
        if ($withdrawal->status === WithdrawRequest::STATUS_CANCELLED) {
            $timeline[] = [
                'timestamp' => $this->formatDate($withdrawal->updated_at),
                'title' => 'Giảng viên đã hủy yêu cầu',
                'description' => 'Lý do: ' . ($withdrawal->rejected_reason ?? 'Người dùng tự hủy.'),
                'status' => 'error',
            ];
        } elseif ($withdrawal->status === WithdrawRequest::STATUS_REJECTED) {
            $timeline[] = [
                'timestamp' => $this->formatDate($withdrawal->updated_at),
                'title' => 'Từ chối yêu cầu',
                'description' => 'Lý do: ' . ($withdrawal->rejected_reason ?? 'Không có lý do cụ thể.'),
                'status' => 'error',
            ];
        }
        if ($withdrawal->paid_at) {
            $timeline[] = [
                'timestamp' => $this->formatDate($withdrawal->paid_at),
                'title' => 'Hoàn tất thanh toán',
                'description' => 'Mã giao dịch nhà cung cấp: ' . ($withdrawal->provider_payout_id ?? 'N/A'),
                'status' => 'success',
            ];
        }

        $data = [
            'id' => $withdrawal->id,
            'withdrawal_code' => 'WD-' . $withdrawal->id,
            'amount' => $amount,
            'status' => $withdrawal->status,
            'requested_at' => $this->formatDate($withdrawal->requested_at),
            'paid_at' => $this->formatDate($withdrawal->paid_at),
            'approved_at' => $this->formatDate($withdrawal->approved_at),
            'rejected_at' => $withdrawal->status === WithdrawRequest::STATUS_REJECTED ? $this->formatDate($withdrawal->updated_at) : null,
            'provider_payout_id' => $withdrawal->provider_payout_id,
            'payout_provider' => $withdrawal->payout_provider,
            'payout_mode' => in_array($withdrawal->status, [WithdrawRequest::STATUS_PAID, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_MANUAL_REQUIRED, WithdrawRequest::STATUS_FAILED])
                ? ($withdrawal->payout_provider === 'manual' ? 'manual' : 'auto')
                : null,
            'rejected_reason' => $withdrawal->rejected_reason,
            'payout_snapshot' => [
                'payout_account_id' => $withdrawal->payout_account_id,
                'account_name' => $withdrawal->account_name_snapshot,
                'account_number' => $withdrawal->account_number_snapshot,
                'account_number_masked' => $this->maskAccountNumber($withdrawal->account_number_snapshot),
                'provider' => $withdrawal->bank_name_snapshot ?: 'Bank Transfer',
                'status' => 'active'
            ],
            'user' => [
                'id' => $withdrawal->user?->id,
                'full_name' => $withdrawal->user?->full_name,
                'email' => $withdrawal->user?->email,
                'avatar_url' => null,
            ],
            'balance_snapshot' => [
                'available_balance_before' => $balanceBefore,
                'holding_balance_before' => $holdingBalance,
                'available_balance_after' => $balanceAfter,
            ],
            'allocations' => $allocations,
            'timeline' => $timeline,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết yêu cầu rút tiền thành công.',
            'data' => $data
        ]);
    }

    /**
     * PATCH /api/admin/withdrawals/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        $withdrawal = WithdrawRequest::find($id);

        if (! $withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu rút tiền.',
            ], 404);
        }

        if ($withdrawal->status !== WithdrawRequest::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể duyệt yêu cầu rút tiền ở trạng thái Chờ duyệt.',
            ], 422);
        }

        $withdrawal->status = WithdrawRequest::STATUS_APPROVED;
        $withdrawal->approved_at = now();
        $withdrawal->save();

        // Initiate Payout Process
        $this->payoutService->process($withdrawal);

        return response()->json([
            'success' => true,
            'message' => 'Duyệt yêu cầu rút tiền thành công. Trạng thái: ' . $withdrawal->status,
        ]);
    }

    /**
     * PATCH /api/admin/withdrawals/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $withdrawal = WithdrawRequest::find($id);

        if (! $withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu rút tiền.',
            ], 404);
        }

        if ($withdrawal->status !== WithdrawRequest::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể từ chối yêu cầu rút tiền ở trạng thái Chờ duyệt.',
            ], 422);
        }

        $withdrawal->status = WithdrawRequest::STATUS_REJECTED;
        $withdrawal->rejected_reason = $request->input('reason');
        $withdrawal->save();

        $this->earlyWithdrawalService->releaseAllocations($withdrawal);

        return response()->json([
            'success' => true,
            'message' => 'Từ chối yêu cầu rút tiền thành công.',
        ]);
    }

    /**
     * PATCH /api/admin/withdrawals/{id}/mark-paid
     */
    public function markPaid(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'provider_payout_id' => ['required', 'string', 'max:255'],
        ]);

        $withdrawal = WithdrawRequest::find($id);

        if (! $withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu rút tiền.',
            ], 404);
        }

        if ($withdrawal->status !== WithdrawRequest::STATUS_MANUAL_REQUIRED) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hoàn tất thanh toán cho yêu cầu ở trạng thái Cần xử lý thủ công.',
            ], 422);
        }
        if (empty($withdrawal->payout_provider)) {
            $withdrawal->payout_provider = 'manual';
            $withdrawal->save();
        }

        $this->payoutService->finalizeSuccess($withdrawal, $request->input('provider_payout_id'));

        return response()->json([
            'success' => true,
            'message' => 'Đánh dấu hoàn tất thanh toán thành công.',
        ]);
    }

    /**
     * PATCH /api/admin/withdrawals/{id}/mark-failed
     */
    public function markFailed(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $withdrawal = WithdrawRequest::find($id);

        if (! $withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu rút tiền.',
            ], 404);
        }

        if ($withdrawal->status !== WithdrawRequest::STATUS_MANUAL_REQUIRED) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể đánh dấu thất bại cho yêu cầu ở trạng thái Cần xử lý thủ công.',
            ], 422);
        }

        $withdrawal->status = WithdrawRequest::STATUS_FAILED;
        $withdrawal->failure_reason = $request->input('reason');
        $withdrawal->save();

        $this->earlyWithdrawalService->releaseAllocations($withdrawal);

        return response()->json([
            'success' => true,
            'message' => 'Đánh dấu thất bại và hoàn tiền thành công.',
        ]);
    }

    /**
     * Mask account numbers to show only last 4 digits
     */
    private function maskAccountNumber(?string $accountNumber): ?string
    {
        if ($accountNumber === null || $accountNumber === '') {
            return null;
        }
        $length = mb_strlen($accountNumber);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        return str_repeat('*', max(0, $length - 4)) . mb_substr($accountNumber, -4);
    }

    /**
     * Format Carbon dates to ISO 8601 string safely
     */
    private function formatDate(mixed $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }
        try {
            return Carbon::parse($date)->toIso8601String();
        } catch (\Exception $e) {
            return null;
        }
    }
}
