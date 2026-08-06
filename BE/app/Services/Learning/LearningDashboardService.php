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

    public function getActivityCalendar(int $userId, int $month, int $year): array
    {
        $streak = $this->learningDashboardRepository->getStreak($userId);
        $dailyMission = $this->learningDashboardRepository->getDailyMission($userId);
        $heatmap = $this->learningDashboardRepository->getHeatmap($userId, $month, $year);

        return [
            'streak' => $streak,
            'daily_mission' => $dailyMission,
            'heatmap' => $heatmap,
        ];
    }
}