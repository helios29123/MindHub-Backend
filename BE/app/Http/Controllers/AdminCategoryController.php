<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\CategoryQueryRequest;
use App\Http\Requests\Admin\ReorderCategoryRequest;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\Admin\AdminCategoryResource;
use App\Services\Admin\AdminCategoryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class AdminCategoryController extends Controller
{
    public function __construct(
        private readonly AdminCategoryService $categoryService,
    ) {
    }

    public function index(CategoryQueryRequest $request): JsonResponse
    {
        $result = $this->categoryService->index($request->validated());
        $paginator = $result['paginator'];

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách danh mục thành công.',
            'data' => [
                'summary' => $result['summary'],
                'items' => AdminCategoryResource::collection($paginator->getCollection())->resolve($request),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(
            new AdminCategoryResource($this->categoryService->show($id)),
            'Lấy chi tiết danh mục thành công.'
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        return ApiResponse::success(
            new AdminCategoryResource($this->categoryService->create($request->validated())),
            'Tạo danh mục thành công.',
            201
        );
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        return ApiResponse::success(
            new AdminCategoryResource($this->categoryService->update($id, $request->validated())),
            'Cập nhật danh mục thành công.'
        );
    }


    public function reorder(ReorderCategoryRequest $request): JsonResponse
    {
        $this->categoryService->reorder($request->validated('items'));
        return ApiResponse::success(null, 'Lưu thứ tự danh mục thành công.');
    }
}