<?php
namespace App\Repositories\Instructor;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\WithdrawRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
final class InstructorWithdrawalRepository
{
    public function availableRevenueAmount(int $instructorId): float
    {
        return (float) Revenue::query()
            ->where('instructor_id', $instructorId)
            ->where('status', 'available')
            ->sum('instructor_amount');
    }
    public function pendingWithdrawAmount(int $instructorId): float
    {
        return (float) WithdrawRequest::query()
            ->where('user_id', $instructorId)
            ->whereIn('status', [
                WithdrawRequest::STATUS_PENDING,
                WithdrawRequest::STATUS_APPROVED,
            ])
            ->sum('amount');
    }
    public function paidWithdrawAmount(int $instructorId): float
    {
        return (float) WithdrawRequest::query()
            ->where('user_id', $instructorId)
            ->where('status', WithdrawRequest::STATUS_PAID)
            ->sum('amount');
    }
    public function activePayoutAccount(int $instructorId): ?PayoutAccount
    {
        return PayoutAccount::query()
            ->where('user_id', $instructorId)
            ->where('status', PayoutAccount::STATUS_ACTIVE)
            ->orderByDesc('id')
            ->first();
    }
    public function payoutAccounts(int $instructorId, ?string $status = null): Collection
    {
        $query = PayoutAccount::query()
            ->where('user_id', $instructorId);
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        } else {
            $query->where('status', PayoutAccount::STATUS_ACTIVE);
        }
        return $query
            ->orderByDesc('id')
            ->get();
    }
    public function paginateWithdrawals(int $instructorId, array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);
        $query = WithdrawRequest::query()
            ->with('payoutAccount')
            ->where('user_id', $instructorId);
        $status = $filters['status'] ?? 'all';
        if ($status !== null && $status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }
        if (!empty($filters['date_from'])) {
            $query->where('requested_at', '>=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $query->where('requested_at', '<=', $filters['date_to'] . ' 23:59:59');
        }
        return $query
            ->orderByDesc('requested_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
    public function findOwnedWithdrawal(int $withdrawalId, int $instructorId): ?WithdrawRequest
    {
        return WithdrawRequest::query()
            ->with('payoutAccount')
            ->where('id', $withdrawalId)
            ->where('user_id', $instructorId)
            ->first();
    }
    public function findPayoutAccountForUpdate(int $payoutAccountId, int $instructorId): ?PayoutAccount
    {
        return PayoutAccount::query()
            ->where('id', $payoutAccountId)
            ->where('user_id', $instructorId)
            ->lockForUpdate()
            ->first();
    }
    public function availableRevenueAmountForUpdate(int $instructorId): float
    {
        $revenues = Revenue::query()
            ->where('instructor_id', $instructorId)
            ->where('status', 'available')
            ->lockForUpdate()
            ->get(['id', 'instructor_amount']);
        return (float) $revenues->sum(fn (Revenue $revenue): float => (float) $revenue->instructor_amount);
    }
    public function pendingWithdrawAmountForUpdate(int $instructorId): float
    {
        $withdrawals = WithdrawRequest::query()
            ->where('user_id', $instructorId)
            ->whereIn('status', [
                WithdrawRequest::STATUS_PENDING,
                WithdrawRequest::STATUS_APPROVED,
            ])
            ->lockForUpdate()
            ->get(['id', 'amount']);
        return (float) $withdrawals->sum(fn (WithdrawRequest $withdrawal): float => (float) $withdrawal->amount);
    }
    public function createWithdrawRequest(array $data): WithdrawRequest
    {
        $withdrawal = WithdrawRequest::query()->create($data);
        return $withdrawal->load('payoutAccount');
    }
}