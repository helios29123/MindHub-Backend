<?php

namespace App\Services\Admin;

use App\Exceptions\BusinessException;
use App\Models\Category;
use App\Repositories\Admin\AdminCategoryRepository;
use Illuminate\Support\Facades\DB;

final class AdminCategoryService
{
    public function __construct(
        private readonly AdminCategoryRepository $categoryRepository,
    ) {
    }

    public function index(array $filters): array
    {
        return [
            'paginator' => $this->categoryRepository->paginate($filters),
            'summary' => $this->categoryRepository->summary(),
        ];
    }

    public function show(int $id): Category
    {
        $category = $this->categoryRepository->findWithRelations($id);

        if (!$category) {
            throw new BusinessException('Không tìm thấy danh mục.', 404);
        }

        return $category;
    }

    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            $this->assertValidParent($data['parent_id'] ?? null);
            $data['sort_order'] ??= $this->categoryRepository->nextSortOrder($data['parent_id'] ?? null);
            $data['status'] ??= 'active';

            return $this->categoryRepository->create($data)
                ->load(['parent', 'children'])
                ->loadCount('courses');
        });
    }

    public function update(int $id, array $data): Category
    {
        return DB::transaction(function () use ($id, $data): Category {
            $category = $this->findOrFail($id);

            if (array_key_exists('parent_id', $data)) {
                $this->assertCanMove($category, $data['parent_id']);
            }

            return $this->categoryRepository->update($category, $data)
                ->load(['parent', 'children'])
                ->loadCount('courses');
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $category = $this->findOrFail($id);

            if ($this->categoryRepository->hasChildren($category)) {
                throw new BusinessException('Không thể xóa danh mục vì vẫn còn danh mục con.', 409);
            }

            if ($this->categoryRepository->hasCourses($category)) {
                throw new BusinessException('Không thể xóa danh mục vì vẫn còn khóa học liên kết.', 409);
            }

            $category->delete();
        });
    }

    public function restore(int $id): Category
    {
        return DB::transaction(function () use ($id): Category {
            $category = $this->categoryRepository->findOnlyTrashed($id);

            if (!$category) {
                throw new BusinessException('Không tìm thấy danh mục đã xóa.', 404);
            }

            $slugConflict = Category::withTrashed()
                ->where('slug', $category->slug)
                ->where('id', '<>', $category->id)
                ->exists();

            if ($slugConflict) {
                throw new BusinessException('Không thể khôi phục vì slug đã được sử dụng.', 409);
            }

            if ($category->parent_id !== null) {
                $parent = $this->categoryRepository->find((int) $category->parent_id);
                if (!$parent || $parent->parent_id !== null) {
                    throw new BusinessException('Không thể khôi phục vì danh mục cha không còn hợp lệ.', 409);
                }
            }

            $category->restore();

            return $category->refresh()
                ->load(['parent', 'children'])
                ->loadCount('courses');
        });
    }

    public function reorder(array $items): void
    {
        DB::transaction(function () use ($items): void {
            $parentMap = $this->categoryRepository->allParentMap();

            foreach ($items as $item) {
                $parentMap->put((int) $item['id'], $item['parent_id'] !== null ? (int) $item['parent_id'] : null);
            }

            foreach ($parentMap as $categoryId => $parentId) {
                if ($parentId === null) {
                    continue;
                }

                if ((int) $categoryId === (int) $parentId || !$parentMap->has($parentId)) {
                    throw new BusinessException('Cấu trúc danh mục không hợp lệ.', 422);
                }

                if ($parentMap->get($parentId) !== null) {
                    throw new BusinessException('Hệ thống chỉ cho phép cây danh mục hai cấp.', 422);
                }
            }

            foreach ($items as $item) {
                Category::query()->whereKey((int) $item['id'])->update([
                    'parent_id' => $item['parent_id'],
                    'sort_order' => (int) $item['sort_order'],
                    'updated_at' => now(),
                ]);
            }
        });
    }

    private function findOrFail(int $id): Category
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new BusinessException('Không tìm thấy danh mục.', 404);
        }

        return $category;
    }

    private function assertValidParent(?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if (!$this->categoryRepository->findActiveRoot($parentId)) {
            throw new BusinessException('Danh mục con chỉ được chọn danh mục gốc làm cha.', 422);
        }
    }

    private function assertCanMove(Category $category, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($category->id === $parentId) {
            throw new BusinessException('Không thể chọn chính danh mục làm cha.', 422);
        }

        $this->assertValidParent($parentId);

        if ($this->categoryRepository->hasChildren($category)) {
            throw new BusinessException('Danh mục đang có con không thể chuyển xuống cấp con.', 422);
        }
    }
}