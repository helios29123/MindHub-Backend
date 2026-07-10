<?php

namespace App\Services\Report;

use App\Repositories\Report\InstructorDashboardRepository;

class InstructorDashboardService
{
    public function __construct(
        private readonly InstructorDashboardRepository $repository
    ) {
    }

    public function getDashboard(int $instructorId, array $filters): array
    {
        return $this->repository->getDashboard($instructorId, $filters);
    }
}