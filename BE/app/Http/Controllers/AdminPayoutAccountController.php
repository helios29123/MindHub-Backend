<?php

namespace App\Http\Controllers;

use App\Models\PayoutAccount;
use App\Models\WithdrawRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminPayoutAccountController extends Controller
{
    /**
     * Format date to ISO 8601 string
     */
    private function formatDate($date): ?string
    {
        if (!$date) return null;
        try {
            return Carbon::parse($date)->toIso8601String();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Mask account number (e.g., 970400100004 -> ********0004)
     */
    private function maskAccountNumber(?string $number): string
    {
        if (!$number) return '******';
        $len = strlen($number);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', $len - 4) . substr($number, -4);
    }

    /**
     * GET /api/admin/payout-accounts
     */
    public function index(Request $request): JsonResponse
    {
        $query = PayoutAccount::with('user');

        // 1. Search filter
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('id', $search)
                  ->orWhere('user_id', $search)
                  ->orWhere('account_name', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search): void {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Provider filter
        if ($request->filled('provider') && $request->input('provider') !== 'all') {
            $provider = $request->input('provider');
            $query->where('provider', $provider);
        }

        // Clone query for computing KPI summary before status filter
        $summaryQuery = clone $query;

        // 3. Status filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            $query->where('status', $status);
        }

        // Compute KPIs
        $totalAccounts = $summaryQuery->count();
        
        $statusCounts = $summaryQuery->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $summary = [
            'total_accounts' => $totalAccounts,
            'pending_verification_count' => $statusCounts['pending_verification'] ?? 0,
            'active_count' => $statusCounts['active'] ?? 0,
            'rejected_count' => $statusCounts['rejected'] ?? 0,
            'inactive_count' => $statusCounts['inactive'] ?? 0,
        ];

        // Sort and Paginate
        $perPage = $request->integer('per_page', 20);
        $paginator = $query->orderBy('updated_at', 'desc')
                           ->paginate($perPage);

        // Transform items to mask account numbers in list
        $items = collect($paginator->items())->map(function ($item) {
            $data = $item->toArray();
            $data['account_number_masked'] = $this->maskAccountNumber($item->account_number);
            
            // Mask full account number in list response for security
            unset($data['account_number']);

            // Format dates
            $data['connected_at'] = $this->formatDate($item->connected_at);
            $data['approved_at'] = $this->formatDate($item->approved_at);
            $data['rejected_at'] = $this->formatDate($item->rejected_at);
            $data['disabled_at'] = $this->formatDate($item->disabled_at);
            $data['created_at'] = $this->formatDate($item->created_at);
            $data['updated_at'] = $this->formatDate($item->updated_at);
            
            if ($item->user) {
                $data['user'] = [
                    'id' => $item->user->id,
                    'full_name' => $item->user->full_name,
                    'email' => $item->user->email,
                    'avatar' => $item->user->avatar,
                ];
            }

            return $data;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách tài khoản nhận tiền thành công.',
            'data' => [
                'summary' => $summary,
                'items' => $items,
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/payout-accounts/{id}
     */
    public function show(int $id): JsonResponse
    {
        $account = PayoutAccount::with('user')->find($id);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản nhận tiền.',
            ], 404);
        }

        // Fetch transaction statistics through this account
        $withdrawalCount = WithdrawRequest::where('payout_account_id', $id)->count();
        $totalPaidAmount = WithdrawRequest::where('payout_account_id', $id)
            ->where('status', WithdrawRequest::STATUS_PAID)
            ->sum('amount');

        // Fetch the last 5 withdraw requests
        $recentWithdrawals = WithdrawRequest::where('payout_account_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($w) {
                return [
                    'id' => $w->id,
                    'withdrawal_code' => "WD-{$w->id}",
                    'amount' => (float) $w->amount,
                    'status' => $w->status,
                    'requested_at' => $this->formatDate($w->requested_at ?: $w->created_at),
                ];
            });

        // Build dynamically status timeline history log
        $timeline = [];
        $timeline[] = [
            'timestamp' => $this->formatDate($account->created_at),
            'title' => 'Khởi tạo tài khoản',
            'description' => 'Giảng viên đăng ký thông tin liên kết tài khoản.',
            'status' => 'info',
        ];

        if ($account->connected_at) {
            $timeline[] = [
                'timestamp' => $this->formatDate($account->connected_at),
                'title' => 'Đã kết nối',
                'description' => 'Yêu cầu kích hoạt/kết nối tài khoản đã được tiếp nhận.',
                'status' => 'info',
            ];
        }

        if ($account->approved_at) {
            $timeline[] = [
                'timestamp' => $this->formatDate($account->approved_at),
                'title' => 'Đã phê duyệt',
                'description' => 'Admin phê duyệt kích hoạt tài khoản.',
                'status' => 'success',
            ];
        }

        if ($account->rejected_at) {
            $timeline[] = [
                'timestamp' => $this->formatDate($account->rejected_at),
                'title' => 'Đã từ chối',
                'description' => $account->reject_reason ? 'Lý do từ chối: ' . $account->reject_reason : 'Admin từ chối xác minh tài khoản.',
                'status' => 'error',
            ];
        }

        if ($account->disabled_at) {
            $timeline[] = [
                'timestamp' => $this->formatDate($account->disabled_at),
                'title' => 'Đã vô hiệu hóa',
                'description' => 'Admin đã vô hiệu hóa tài khoản.',
                'status' => 'warning',
            ];
        }

        // Return full unmasked account number for drawer detail
        $data = $account->toArray();
        $data['account_number_masked'] = $this->maskAccountNumber($account->account_number);
        $data['withdrawal_count'] = $withdrawalCount;
        $data['total_paid_amount'] = (float) $totalPaidAmount;
        $data['related_withdrawals'] = $recentWithdrawals;
        $data['timeline'] = array_reverse($timeline); // Latest events first

        // Format dates
        $data['connected_at'] = $this->formatDate($account->connected_at);
        $data['approved_at'] = $this->formatDate($account->approved_at);
        $data['rejected_at'] = $this->formatDate($account->rejected_at);
        $data['disabled_at'] = $this->formatDate($account->disabled_at);
        $data['created_at'] = $this->formatDate($account->created_at);
        $data['updated_at'] = $this->formatDate($account->updated_at);

        if ($account->user) {
            $data['user'] = [
                'id' => $account->user->id,
                'full_name' => $account->user->full_name,
                'email' => $account->user->email,
                'avatar' => $account->user->avatar,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết tài khoản nhận tiền thành công.',
            'data' => $data,
        ]);
    }

    /**
     * PATCH /api/admin/payout-accounts/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        $account = PayoutAccount::find($id);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản nhận tiền.',
            ], 404);
        }

        if ($account->status !== 'pending_verification') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể phê duyệt tài khoản ở trạng thái Chờ xác minh.',
            ], 422);
        }

        $account->status = 'active';
        $account->approved_at = now();
        $account->connected_at = now();
        $account->save();

        return response()->json([
            'success' => true,
            'message' => 'Duyệt tài khoản nhận tiền thành công.',
        ]);
    }

    /**
     * PATCH /api/admin/payout-accounts/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $account = PayoutAccount::find($id);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản nhận tiền.',
            ], 404);
        }

        if ($account->status !== 'pending_verification') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể từ chối tài khoản ở trạng thái Chờ xác minh.',
            ], 422);
        }

        $account->status = 'rejected';
        $account->rejected_at = now();
        if ($request->filled('reason')) {
            $account->reject_reason = $request->input('reason');
        }
        $account->save();

        return response()->json([
            'success' => true,
            'message' => 'Từ chối tài khoản nhận tiền thành công.',
        ]);
    }

    /**
     * PATCH /api/admin/payout-accounts/{id}/disable
     */
    public function disable(int $id): JsonResponse
    {
        $account = PayoutAccount::find($id);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản nhận tiền.',
            ], 404);
        }

        if ($account->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể vô hiệu hóa tài khoản đang ở trạng thái Đang hoạt động.',
            ], 422);
        }

        $account->status = 'inactive';
        $account->disabled_at = now();
        $account->save();

        return response()->json([
            'success' => true,
            'message' => 'Vô hiệu hóa tài khoản nhận tiền thành công.',
        ]);
    }
}
