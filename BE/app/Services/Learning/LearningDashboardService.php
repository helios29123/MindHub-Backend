<?php

namespace App\Services\Learning;

use App\Repositories\Learning\LearningDashboardRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LearningDashboardService
{
    public function __construct(
        private readonly LearningDashboardRepository $learningDashboardRepository
    ) {
    }

    public function getDashboard(int $userId, array $filters = []): array
    {
        $statistics = $this->learningDashboardRepository->getStatistics($userId);
        $recentCourse = $this->learningDashboardRepository->getRecentCourse($userId);

        return [
            'statistics' => $statistics,
            'recent_course' => $recentCourse,
        ];
    }
}