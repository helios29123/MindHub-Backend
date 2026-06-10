<?php
namespace App\Repositories\Catalog;

class BannerRepository
{
   public function getActiveHomeBanners()
{
    return \App\Models\Banner::query()
        ->where('status', 'active')
        ->where('position', 'home')
        ->whereNull('deleted_at')
        ->where(function ($query) {
            $query->whereNull('start_at')
                ->orWhere('start_at', '<=', now());
        })
        ->where(function ($query) {
            $query->whereNull('end_at')
                ->orWhere('end_at', '>=', now());
        })
        ->orderBy('sort_order')
        ->orderByDesc('id')
        ->get();
}
}
