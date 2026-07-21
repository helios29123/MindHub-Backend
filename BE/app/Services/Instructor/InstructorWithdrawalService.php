<?php

namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Models\WithdrawRequest;
use App\Models\PayoutAccount;
use App\Repositories\Instructor\InstructorWithdrawalRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InstructorWithdrawalService
{
    public function __construct(
        private readonly InstructorWithdrawalRepository $repository
    ) {
    }

    public function summary(int $instructorId, array $filters = []): array
    {
        return $this->repository->getSummary($instructorId);
    }

    public function paginate(int $instructorId, array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateWithdrawals($instructorId, $filters);
    }

    public function show(int $instructorId, int $withdrawalId): ?WithdrawRequest
    {
        return $this->repository->getWithdrawalDetail($instructorId, $withdrawalId);
    }

    public function store(int $instructorId, array $data): ?WithdrawRequest
    {
        $payoutAccountId = (int) $data['payout_account_id'];
        $amount = (float) $data['amount'];

        $payoutAccount = PayoutAccount::where('id', $payoutAccountId)
            ->where('user_id', $instructorId)
            ->first();

        if (!$payoutAccount) {
            throw new BusinessException('Không tìm thấy tài khoản nhận tiền.', 404);
        }

        if ($payoutAccount->status !== PayoutAccount::STATUS_ACTIVE) {
            throw new BusinessException('Tài khoản nhận tiền chưa được kích hoạt hoặc xác minh.', 422);
        }

        if ($amount < 200000) {
            throw new BusinessException('Số tiền rút tối thiểu là 200,000đ.', 422);
        }

        $summary = $this->summary($instructorId);
        $withdrawableBalance = (float) $summary['available_balance'];

        if ($amount > $withdrawableBalance) {
            throw new BusinessException('Số tiền rút vượt quá số dư khả dụng.', 409);
        }

        $payload = [
            'amount' => $amount,
            'account_number' => $payoutAccount->account_number,
            'account_name' => $payoutAccount->account_name,
            'payout_account_id' => $payoutAccountId,
        ];

        return $this->repository->createWithdrawal($instructorId, $payload);
    }

    public function payoutAccounts(int $instructorId, array $filters): Collection
    {
        return $this->repository->getPayoutAccounts($instructorId, $filters);
    }

    public function cancel(int $instructorId, int $withdrawalId): bool
    {
        $withdrawal = WithdrawRequest::where('user_id', $instructorId)
            ->where('id', $withdrawalId)
            ->first();

        if (!$withdrawal) {
            return false;
        }

        if ($withdrawal->status !== WithdrawRequest::STATUS_PENDING) {
            throw new BusinessException('Chỉ có thể hủy yêu cầu rút tiền đang chờ xử lý.', 422);
        }

        $this->repository->cancelWithdrawal($instructorId, $withdrawalId);
        return true;
    }
}
