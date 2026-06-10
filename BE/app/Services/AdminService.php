<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminService
{
    public function getBanners(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        return Banner::orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function getBanner(int $id): Banner
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        return $banner;
    }

    public function createBanner(array $data): Banner
    {
        return Banner::create($data);
    }

    public function updateBanner(int $id, array $data): Banner
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $banner->update($data);
        return $banner;
    }

    public function deleteBanner(int $id): void
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        $banner->delete();
    }
}
