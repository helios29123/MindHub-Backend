<?php

namespace App\Services\Instructor;

use App\Repositories\Instructor\InstructorWithdrawalRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
    public function show(int $instructorId, int $withdrawalId): ?array
    {
        return $this->repository->getWithdrawalDetail($instructorId, $withdrawalId);
    }
    public function store(int $instructorId, array $data): ?array
    {
        return $this->repository->createWithdrawal($instructorId, $data);
    }
    public function payoutAccounts(int $instructorId): ?array
    {
        return $this->repository->getPayoutAccount($instructorId);
    }

}
