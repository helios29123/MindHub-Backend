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

    public function getSummary(int $instructorId, array $filters = []): array
    {
        return $this->repository->getSummary($instructorId);
    }

    public function paginateWithdrawals(int $instructorId, array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateWithdrawals($instructorId, $filters);
    }
}