<?php

namespace App\Services\Admin;

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

    public function getCategories(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        $query = \App\Models\Category::query()->with(['parent', 'children']);

        if (!empty($queryParams['search'])) {
            $search = trim((string) $queryParams['search']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (!empty($queryParams['status'])) {
            $query->where('status', $queryParams['status']);
        }

        if (array_key_exists('parent_id', $queryParams)) {
            $query->where('parent_id', $queryParams['parent_id']);
        }

        $sortBy = $queryParams['sort_by'] ?? 'sort_order';
        $sortDirection = $queryParams['sort_direction'] ?? 'asc';

        return $query->orderBy($sortBy, $sortDirection)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getCategory(int $id): \App\Models\Category
    {
        $category = \App\Models\Category::with(['parent', 'children'])->find($id);

        if (!$category) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        return $category;
    }

    public function createCategory(array $data): \App\Models\Category
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data): \App\Models\Category {
            if (!isset($data['sort_order']) || $data['sort_order'] === null) {
                $maxSortOrder = \App\Models\Category::where('parent_id', $data['parent_id'] ?? null)->max('sort_order');
                $data['sort_order'] = ((int) $maxSortOrder) + 1;
            }

            $data['status'] ??= 'active';

            return \App\Models\Category::create($data)->load(['parent', 'children']);
        });
    }

    public function updateCategory(int $id, array $data): \App\Models\Category
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $data): \App\Models\Category {
            $category = \App\Models\Category::find($id);

            if (!$category) {
                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
            }

            if (array_key_exists('parent_id', $data) && $data['parent_id'] !== $category->parent_id) {
                if ((int) $data['parent_id'] === $category->id) {
                    throw new BusinessException('Danh mục cha không hợp lệ.', 422);
                }

                // Check cyclic dependency
                $currentParentId = $data['parent_id'];
                while ($currentParentId !== null) {
                    if ((int) $currentParentId === $category->id) {
                        throw new BusinessException('Không thể tạo vòng lặp danh mục.', 422);
                    }
                    $parent = \App\Models\Category::find($currentParentId);
                    $currentParentId = $parent ? $parent->parent_id : null;
                }
            }

            $allowedFields = ['name', 'slug', 'parent_id', 'description', 'sort_order', 'status'];
            $updateData = [];
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            $category->update($updateData);

            return $category->refresh()->load(['parent', 'children']);
        });
    }

    public function deleteCategory(int $id): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($id): void {
            $category = \App\Models\Category::with('children', 'courses')->find($id);

            if (!$category) {
                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
            }

            if ($category->children->count() > 0) {
                throw new BusinessException('Không thể xóa danh mục đang có danh mục con.', 400);
            }

            if ($category->courses->count() > 0) {
                throw new BusinessException('Không thể xóa danh mục đang có khóa học liên kết.', 400);
            }

            $category->delete();
        });
    }

    public function getCourses(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
        $query = \App\Models\Course::query()->with(['instructor', 'categories']);

        if (!empty($queryParams['search'])) {
            $search = trim((string) $queryParams['search']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($queryParams['status'])) {
            $query->where('status', $queryParams['status']);
        }

        if (!empty($queryParams['instructor_id'])) {
            $query->where('instructor_id', $queryParams['instructor_id']);
        }

        if (!empty($queryParams['category_id'])) {
            $query->whereHas('categories', function ($builder) use ($queryParams): void {
                $builder->where('categories.id', $queryParams['category_id']);
            });
        }

        if (!empty($queryParams['level'])) {
            $query->where('level', $queryParams['level']);
        }

        $sortBy = $queryParams['sort_by'] ?? 'created_at';
        $sortDirection = $queryParams['sort_direction'] ?? 'desc';

        return $query->orderBy($sortBy, $sortDirection)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getCourse(int $id): \App\Models\Course
    {
        $course = \App\Models\Course::with(['instructor', 'categories'])->find($id);

        if (!$course) {
            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
        }

        return $course;
    }

    public function updateCourse(int $id, array $data): \App\Models\Course
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $data): \App\Models\Course {
            $course = \App\Models\Course::find($id);

            if (!$course) {
                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
            }

            $effectivePrice = array_key_exists("price", $data) ? $data["price"] : $course->price;
            $effectiveSalePrice = array_key_exists("sale_price", $data) ? $data["sale_price"] : $course->sale_price;

            if ($effectiveSalePrice !== null && (float) $effectiveSalePrice > (float) $effectivePrice) {
                throw new BusinessException("Giá khuyến mãi không được lớn hơn giá gốc.", 422);
            }

            $allowedFields = [
                'status',
                'is_featured',
                'admin_reject_reason',
                'title',
                'slug',
                'short_description',
                'description',
                'thumbnail_url',
                'intro_video_url',
                'price',
                'sale_price',
                'level',
                'language',
                'requirements',
                'outcomes'
            ];

            $updateData = [];
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (isset($updateData['status']) && $updateData['status'] === 'published' && $course->published_at === null) {
                $updateData['published_at'] = now();
            }

            $course->update($updateData);

            return $course->refresh()->load(['instructor', 'categories']);
        });
    }
}
