<?php

namespace App\Repositories\Instructor;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InstructorWithdrawalRepository
{
    public function getSummary(int $instructorId): array
    {
        $availableRevenue = (float) DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->where('status', 'available')
            ->sum('instructor_amount');

        $pendingWithdrawAmount = (float) DB::table('withdraw_requests')
            ->where('user_id', $instructorId)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        $payoutAccount = DB::table('payout_accounts')
            ->where('user_id', $instructorId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->first();

        return [
            'available_revenue' => $this->money($availableRevenue),
            'pending_withdraw_amount' => $this->money($pendingWithdrawAmount),
            'available_balance' => $this->money(max($availableRevenue - $pendingWithdrawAmount, 0)),
            'payout_account' => $payoutAccount ? [
                'id' => (int) $payoutAccount->id,
                'provider' => $payoutAccount->provider,
                'account_number_masked' => $this->maskAccount((string) $payoutAccount->account_number),
                'account_name' => $payoutAccount->account_name,
                'status' => $payoutAccount->status,
            ] : null,
        ];
    }

    public function paginateWithdrawals(int $instructorId, array $filters): LengthAwarePaginator
    {
        $page = max((int) ($filters['page'] ?? 1), 1);
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);

        $query = DB::table('withdraw_requests')
            ->where('user_id', $instructorId)
            ->select([
                'id',
                'amount',
                'status',
                'requested_at',
                'approved_at',
                'paid_at',
                'rejected_reason',
                'provider_payout_id',
                'account_number_snapshot',
                'account_name_snapshot',
            ]);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('requested_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('requested_at', '<=', $filters['date_to']);
        }

        $paginator = $query->orderByDesc('requested_at')->paginate($perPage, ['*'], 'page', $page);

        $paginator->getCollection()->transform(function ($row) {
            $row->account_number_masked = $this->maskAccount((string) $row->account_number_snapshot);
            unset($row->account_number_snapshot);

            return $row;
        });

        return $paginator;
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