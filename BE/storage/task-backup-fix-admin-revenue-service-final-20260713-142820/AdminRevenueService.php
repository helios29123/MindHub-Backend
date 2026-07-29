<?php
namespace App\Services\Admin;
use App\Models\Revenue;
use App\Repositories\Admin\AdminRevenueRepository;
final class AdminRevenueService
{
    public function __construct(private readonly AdminRevenueRepository $repo)
    {
    }
    public function paginate(array $filters)
    {
        return $this->repo->paginate($filters);
    }
    public function summary(array $filters): array
    {
        return $this->repo->summary($filters);
    }
    public function sourceBreakdown(array $filters): array
    {
        return $this->repo->sourceBreakdown($filters);
    }
    public function chart(array $filters): array
    {
        return $this->repo->chart($filters);
    }
    public function show(Revenue $revenue): Revenue
    {
        return $revenue->load(['course', 'instructor', 'order']);
    }
}
