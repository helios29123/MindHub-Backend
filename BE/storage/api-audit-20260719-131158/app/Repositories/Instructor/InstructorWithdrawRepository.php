<?php

namespace App\Repositories\Instructor;

use App\Models\PayoutAccount;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\DB;

class InstructorWithdrawRepository
{
    public function findActivePayoutAccountForUser(int $payoutAccountId, int $userId): ?PayoutAccount
    {
        return PayoutAccount::query()
            ->whereKey($payoutAccountId)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->first();
    }

    public function getAvailableRevenueAmount(int $instructorId): float
    {
        return (float) DB::table('revenues')
            ->where('instructor_id', $instructorId)
            ->where('status', 'available')
            ->sum('instructor_amount');
    }

    public function getReservedWithdrawAmount(int $userId): float
    {
        return (float) DB::table('withdraw_requests')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');
    }

    public function createWithdrawRequest(array $data): WithdrawRequest
    {
        return WithdrawRequest::query()->create($data);
    }
}