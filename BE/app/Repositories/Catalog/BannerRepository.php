<?php
namespace App\Repositories\Catalog;
use App\Models\Banner;
use Illuminate\Support\Collection;

class BannerRepository
{
    public function activeHomeBanners(): Collection
    {
        $now = now();

        return Banner::query()
            ->where('status', 'active')
            ->where('position', 'home')
            ->where(function ($query) use ($now) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get(['id', 'title', 'image_url', 'target_url', 'position', 'sort_order']);
    }
}
