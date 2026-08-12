<?php

namespace App\Services\Report;

use App\Repositories\Report\InstructorDashboardAlertRepository;

class InstructorDashboardAlertService
{
    public function __construct(
        private readonly InstructorDashboardAlertRepository $repository
    ) {
    }

    public function getAlerts(int $instructorId, array $filters): array
    {
        return $this->repository->getAlerts($instructorId, $filters);
    }
}