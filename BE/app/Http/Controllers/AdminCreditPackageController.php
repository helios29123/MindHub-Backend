<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreCourseCreditPackageRequest;
use App\Http\Requests\Admin\UpdateCourseCreditPackageRequest;
use App\Models\CourseCreditPackage;
use Illuminate\Http\JsonResponse;

class AdminCreditPackageController extends Controller
{
    public function index(): JsonResponse
    {
        $packages = CourseCreditPackage::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate((int) request('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    public function store(StoreCourseCreditPackageRequest $request): JsonResponse
    {
        $package = CourseCreditPackage::query()->create([
            'name' => $request->string('name')->toString(),
            'description' => $request->input('description'),
            'credits' => (int) $request->input('credits'),
            'price' => (float) $request->input('price'),
            'status' => $request->input('status', CourseCreditPackage::STATUS_ACTIVE),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo gói lượt thành công.',
            'data' => $package,
        ], 201);
    }

    public function update(UpdateCourseCreditPackageRequest $request, int $packageId): JsonResponse
    {
        $package = CourseCreditPackage::query()->findOrFail($packageId);

        $data = $request->validated();

        $package->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật gói lượt thành công.',
            'data' => $package->fresh(),
        ]);
    }

    public function destroy(int $packageId): JsonResponse
    {
        $package = CourseCreditPackage::query()->findOrFail($packageId);

        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa gói lượt thành công.',
        ]);
    }
}
