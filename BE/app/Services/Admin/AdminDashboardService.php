<?php

namespace App\Services\Admin;

use App\Repositories\Admin\AdminDashboardRepository;

final class AdminDashboardService
{
    public function __construct(private readonly AdminDashboardRepository $repo) {}
    public function overview(array $filters): array
    {
        return ['kpis' => $this->repo->kpis(), 'source_breakdown' => $this->repo->sourceBreakdown(), 'revenue_chart' => $this->repo->revenueChart()];
    }
    public function revenueChart(array $filters): array
    {
        return $this->repo->revenueChart();
    }
    public function actionRequired(): array
    {
        return $this->repo->kpis();
    }
}
