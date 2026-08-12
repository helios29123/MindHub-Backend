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
    public function sourceBreakdown(array $filters = []): array
{
    return $this->repo
        ->sourceBreakdown($filters)
        ->map(function ($item): array {
            return [
                'sale_channel' => $item->sale_channel ?? 'unknown',
                'gross_amount' => (float) ($item->gross_amount ?? 0),
                'instructor_amount' => (float) ($item->instructor_amount ?? 0),
                'platform_fee_amount' => (float) ($item->platform_fee_amount ?? 0),
                'total' => (int) ($item->total ?? 0),
            ];
        })
        ->values()
        ->toArray();
}


    public function chart(array $filters = []): array
{
    return $this->repo
        ->chart($filters)
        ->map(function ($item): array {
            return [
                'month' => $item->month,
                'gross_amount' => (float) ($item->gross_amount ?? 0),
                'instructor_amount' => (float) ($item->instructor_amount ?? 0),
                'platform_fee_amount' => (float) ($item->platform_fee_amount ?? 0),
                'total' => (int) ($item->total ?? 0),
            ];
        })
        ->values()
        ->toArray();
}


    public function show(Revenue $revenue): Revenue
    {
        return $revenue->load(['course', 'instructor', 'order']);
    }
}
