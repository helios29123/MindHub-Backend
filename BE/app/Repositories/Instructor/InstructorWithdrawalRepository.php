<?php

namespace App\Repositories\Instructor;

use App\Models\WithdrawRequest;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InstructorWithdrawalRepository
{
    public function getSummary(int $instructorId): array
    {
        $availableRevenue = (float) Revenue::where('instructor_id', $instructorId)
            ->where('status', 'available')
            ->sum('instructor_amount');

        $pendingWithdrawAmount = (float) WithdrawRequest::where('user_id', $instructorId)
            ->whereIn('status', [WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_APPROVED])
            ->sum('amount');

        $paidWithdrawAmount = (float) WithdrawRequest::where('user_id', $instructorId)
            ->where('status', WithdrawRequest::STATUS_PAID)
            ->sum('amount');

        $availableBalance = max($availableRevenue - $pendingWithdrawAmount, 0);

        $payoutAccount = PayoutAccount::where('user_id', $instructorId)
            ->where('status', PayoutAccount::STATUS_ACTIVE)
            ->orderByDesc('updated_at')
            ->first();

        $hasActivePayoutAccount = ($payoutAccount !== null);
        $canCreateWithdrawal = $hasActivePayoutAccount && ($availableBalance >= 200000);

        $notice = null;
        if (!$hasActivePayoutAccount) {
            $notice = 'Bạn cần thêm tài khoản nhận tiền trước khi tạo yêu cầu rút.';
        }

        return [
            'available_revenue' => $availableRevenue,
            'pending_withdraw_amount' => $pendingWithdrawAmount,
            'paid_withdraw_amount' => $paidWithdrawAmount,
            'available_balance' => $availableBalance,
            'can_create_withdrawal' => $canCreateWithdrawal,
            'payout_account' => $payoutAccount,
            'notice' => $notice,
        ];
    }

    public function paginateWithdrawals(int $instructorId, array $filters): LengthAwarePaginator
    {
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);

        $query = WithdrawRequest::where('user_id', $instructorId);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('requested_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('requested_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('requested_at')->paginate($perPage, ['*'], 'page', $page);
    }

    public function getWithdrawalDetail(int $instructorId, int $withdrawalId): ?WithdrawRequest
    {
        return WithdrawRequest::with('payoutAccount')
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
            'status' => WithdrawRequest::STATUS_PENDING,
            'requested_at' => now(),
            'account_number_snapshot' => $data['account_number'],
            'account_name_snapshot' => $data['account_name'],
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

        if (!$withdrawal) {
            return false;
        }

        if ($withdrawal->status !== WithdrawRequest::STATUS_PENDING) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException('Chỉ có thể hủy yêu cầu rút tiền đang chờ xử lý.');
        }

        $withdrawal->update([
            'status' => WithdrawRequest::STATUS_CANCELLED,
            'updated_at' => now(),
        ]);

        return true;
    }

    private function maskAccount(string $accountNumber): string
    {
        $length = strlen($accountNumber);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', max($length - 4, 0)) . substr($accountNumber, -4);
    }

    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
