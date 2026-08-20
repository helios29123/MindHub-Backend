<?php

namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Models\PayoutAccount;
use App\Models\WithdrawRequest;
use App\Repositories\Instructor\InstructorWithdrawalRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

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

    /**
     * Manual withdrawal creation is DEPRECATED in Udemy periodic payout model.
     */
    public function store(int $instructorId, array $data): ?WithdrawRequest
    {
        throw new BusinessException('Hệ thống thanh toán giảng viên theo kỳ và không hỗ trợ rút tiền thủ công.', 422);
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

        if (! $withdrawal) {
            return false;
        }

        if ($withdrawal->status !== WithdrawRequest::STATUS_PENDING) {
            throw new BusinessException('Chỉ có thể hủy đợt thanh toán chưa xử lý.', 422);
        }

        $this->repository->cancelWithdrawal($instructorId, $withdrawalId);
        return true;
    }
}
