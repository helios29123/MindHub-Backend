<?php
namespace App\Http\Controllers;
use App\Http\Requests\Admin\AdminOrderQueryRequest;
use App\Http\Requests\Admin\BannerRequest;
use App\Http\Resources\Admin\AdminOrderResource;
use App\Http\Resources\BannerResource;
use App\Services\Admin\AdminOrderService;
use App\Services\AdminService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class AdminController extends Controller
{
    public function __construct(
        private readonly AdminService $adminService,
        private readonly AdminOrderService $adminOrderService
    ) {
    }
    public function orders(AdminOrderQueryRequest $request): JsonResponse
    {
        $orders = $this->adminOrderService->paginateOrders($request->validated());
        return ApiResponse::paginated(
            AdminOrderResource::collection($orders),
            $orders,
            'Lấy danh sách giao dịch và đơn hàng thành công.'
        );
    }
    public function banners(Request $request, mixed $id = null): JsonResponse
    {
        // 1. Validate path parameter ID if present
        if ($id !== null) {
            $pathValidator = Validator::make(['id' => $id], [
                'id' => 'required|integer|min:1',
            ]);
            if ($pathValidator->fails()) {
                return ApiResponse::error('Dữ liệu không hợp lệ.', $pathValidator->errors()->toArray(), 422);
            }
            $id = (int) $id;
        }
        // 2. Handle GET for single item details
        if ($request->isMethod('get') && $id !== null) {
            $banner = $this->adminService->getBanner($id);
            return ApiResponse::success(
                new BannerResource($banner),
                'Thao tác thành công',
                200
            );
        }
        // 3. Handle GET for listing paginated
        if ($request->isMethod('get')) {
            $queryValidator = Validator::make($request->query(), [
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);
            if ($queryValidator->fails()) {
                return ApiResponse::error('Dữ liệu không hợp lệ.', $queryValidator->errors()->toArray(), 422);
            }
            $banners = $this->adminService->getBanners($queryValidator->validated());
            return ApiResponse::paginated(
                BannerResource::collection($banners),
                $banners,
                'Thao tác thành công'
            );
        }
        // 4. Handle POST for creating
        if ($request->isMethod('post')) {
            $bannerRequest = app(BannerRequest::class);
            $validator = Validator::make($request->all(), $bannerRequest->rules(), $bannerRequest->messages());
            if ($validator->fails()) {
                $errors = $validator->errors();
                $message = 'Dữ liệu không hợp lệ.';
                if ($errors->has('status')) {
                    $message = 'Trạng thái banner không hợp lệ.';
                } elseif ($errors->has('end_at')) {
                    $message = 'Thời gian banner không hợp lệ.';
                }
                return ApiResponse::error($message, $errors->toArray(), 422);
            }
            $banner = $this->adminService->createBanner($validator->validated());
            return ApiResponse::success(
                json_encode(['id' => $banner->id, 'status' => 'updated']),
                'Thao tác thành công',
                200
            );
        }
        // 5. Handle PUT/PATCH for updating
        if ($request->isMethod('put') || $request->isMethod('patch')) {
            $bannerRequest = app(BannerRequest::class);
            $validator = Validator::make($request->all(), $bannerRequest->rules(), $bannerRequest->messages());
            if ($validator->fails()) {
                $errors = $validator->errors();
                $message = 'Dữ liệu không hợp lệ.';
                if ($errors->has('status')) {
                    $message = 'Trạng thái banner không hợp lệ.';
                } elseif ($errors->has('end_at')) {
                    $message = 'Thời gian banner không hợp lệ.';
                }
                return ApiResponse::error($message, $errors->toArray(), 422);
            }
            $banner = $this->adminService->updateBanner($id, $validator->validated());
            return ApiResponse::success(
                json_encode(['id' => $banner->id, 'status' => 'updated']),
                'Thao tác thành công',
                200
            );
        }
        // 6. Handle DELETE for destroying
        if ($request->isMethod('delete') && $id !== null) {
            $this->adminService->deleteBanner($id);
            return ApiResponse::success(
                null,
                'Thao tác thành công',
                200
            );
        }
        return ApiResponse::error('Phương thức không được hỗ trợ.', [], 405);
    }
}