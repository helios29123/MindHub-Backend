<?php

namespace App\Repositories\Instructor;

use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InstructorWithdrawalRepository
{
    public function getSummary(int $instructorId): array
    {
        $pendingRevenue = (float) Revenue::where('instructor_id', $instructorId)
            ->where('status', Revenue::STATUS_PENDING)
            ->sum('instructor_amount');

        $availableBalance = (float) Revenue::where('instructor_id', $instructorId)
            ->where('status', Revenue::STATUS_AVAILABLE)
            ->whereNull('payout_id')
            ->sum('instructor_amount');

        $scheduledPayout = (float) WithdrawRequest::where('user_id', $instructorId)
            ->whereIn('status', [WithdrawRequest::STATUS_READY_TO_PAY, WithdrawRequest::STATUS_QUEUED, WithdrawRequest::STATUS_PROCESSING])
            ->sum('amount');

        $paidAmount = (float) WithdrawRequest::where('user_id', $instructorId)
            ->where('status', WithdrawRequest::STATUS_PAID)
            ->sum('amount');

        $blockedAmount = (float) WithdrawRequest::where('user_id', $instructorId)
            ->where('status', WithdrawRequest::STATUS_BLOCKED)
            ->sum('amount');

        $payoutAccount = PayoutAccount::where('user_id', $instructorId)
            ->where('status', PayoutAccount::STATUS_ACTIVE)
            ->orderByDesc('updated_at')
            ->first();

        $minimumPayout = (float) config('revenue.payout.minimum_amount', 200000);
        $hasActivePayoutAccount = ($payoutAccount !== null);

        $accountStatus = 'missing';
        if ($hasActivePayoutAccount) {
            $accountStatus = (! empty($payoutAccount->approved_at) || $payoutAccount->status === PayoutAccount::STATUS_ACTIVE) ? 'verified' : 'unverified';
        }

        $blockedReason = null;
        $verificationWarning = null;

        if (! $hasActivePayoutAccount) {
            $blockedReason = 'Tài khoản nhận tiền chưa được đăng ký hoặc kích hoạt.';
            $verificationWarning = 'Bạn chưa cài đặt tài khoản nhận tiền. Vui lòng cập nhật tài khoản để hệ thống tự động thanh toán.';
        } elseif ($accountStatus === 'unverified') {
            $blockedReason = 'Tài khoản nhận tiền chưa được xác minh OTP/Admin phê duyệt.';
            $verificationWarning = 'Tài khoản nhận tiền của bạn chưa hoàn tất xác minh. Vui lòng kiểm tra lại.';
        } elseif ($availableBalance < $minimumPayout) {
            $blockedReason = 'Số dư khả dụng chưa đạt ngưỡng thanh toán tối thiểu (200.000 VNĐ).';
            $verificationWarning = 'Số dư khả dụng chưa đạt mức thanh toán tối thiểu (200.000 VNĐ). Số dư sẽ được bảo lưu và cộng dồn sang kỳ sau.';
        }

        $startDay = (int) config('revenue.payout.window_start_day', 5);
        $endDay = (int) config('revenue.payout.window_end_day', 10);
        $nextMonth = now()->addMonth();

        $nextWindow = [
            'from' => (clone $nextMonth)->day($startDay)->toDateString(),
            'to' => (clone $nextMonth)->day($endDay)->toDateString(),
            'formatted' => sprintf('Từ %02d/%02d/%d đến %02d/%02d/%d', $startDay, $nextMonth->month, $nextMonth->year, $endDay, $nextMonth->month, $nextMonth->year),
        ];

        return [
            'page_title' => 'Thanh toán giảng viên',
            'pending_revenue' => round($pendingRevenue, 2),
            'available_balance' => round($availableBalance, 2),
            'scheduled_payout' => round($scheduledPayout, 2),
            'paid_amount' => round($paidAmount, 2),
            'blocked_amount' => round($blockedAmount, 2),
            'minimum_payout' => $minimumPayout,
            'minimum_payout_label' => number_format($minimumPayout, 0, ',', '.') . ' VNĐ',
            'next_payout_window' => $nextWindow,
            'expected_payment_date' => $nextWindow['formatted'],
            'revenue_period' => 'Tháng ' . now()->format('m/Y'),
            'blocked_reason' => $blockedReason,
            'verification_warning' => $verificationWarning,
            'payout_account_status' => $accountStatus,
            'account_update_url' => '/instructor/payout-accounts',
            'payout_account' => $payoutAccount,
            'cards' => [
                'pending_revenue' => [
                    'key' => 'pending_revenue',
                    'label' => 'Doanh thu đang chờ',
                    'amount' => round($pendingRevenue, 2),
                ],
                'available_balance' => [
                    'key' => 'available_balance',
                    'label' => 'Số dư khả dụng',
                    'amount' => round($availableBalance, 2),
                ],
                'scheduled_payout' => [
                    'key' => 'scheduled_payout',
                    'label' => 'Thanh toán sắp tới',
                    'amount' => round($scheduledPayout, 2),
                ],
                'paid_amount' => [
                    'key' => 'paid_amount',
                    'label' => 'Tổng đã thanh toán',
                    'amount' => round($paidAmount, 2),
                ],
                'blocked_amount' => [
                    'key' => 'blocked_amount',
                    'label' => 'Khoản bị tạm giữ',
                    'amount' => round($blockedAmount, 2),
                ],
            ],
        ];
    }

    public function paginateWithdrawals(int $instructorId, array $filters): LengthAwarePaginator
    {
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);

        $query = WithdrawRequest::where('user_id', $instructorId);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('requested_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('requested_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('requested_at')->paginate($perPage, ['*'], 'page', $page);
    }

    public function getWithdrawalDetail(int $instructorId, int $withdrawalId): ?WithdrawRequest
    {
        return WithdrawRequest::with(['payoutAccount', 'revenues.course'])
            ->where('user_id', $instructorId)
            ->where('id', $withdrawalId)
            ->first();
    }

    public function createWithdrawal(int $instructorId, array $data): ?WithdrawRequest
    {
        $withdrawal = WithdrawRequest::create([
            'user_id' => $instructorId,
            'payout_account_id' => $data['payout_account_id'] ?? null,
            'amount' => $data['amount'],
            'status' => WithdrawRequest::STATUS_READY_TO_PAY,
            'requested_at' => now(),
            'account_number_snapshot' => $data['account_number'] ?? null,
            'account_name_snapshot' => $data['account_name'] ?? null,
        ]);

        return $this->getWithdrawalDetail($instructorId, (int) $withdrawal->id);
    }

    public function getPayoutAccounts(int $instructorId, array $filters): Collection
    {
        $query = PayoutAccount::where('user_id', $instructorId);

        $status = $filters['status'] ?? PayoutAccount::STATUS_ACTIVE;
        $query->where('status', $status);

        return $query->orderByDesc('updated_at')->get();
    }

    public function cancelWithdrawal(int $instructorId, int $withdrawalId): bool
    {
        $withdrawal = WithdrawRequest::where('user_id', $instructorId)
            ->where('id', $withdrawalId)
            ->first();

        if (! $withdrawal) {
            return false;
        }

        $withdrawal->update([
            'status' => WithdrawRequest::STATUS_CANCELLED,
            'updated_at' => now(),
        ]);

        return true;
    }
}
