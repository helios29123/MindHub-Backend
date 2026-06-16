<?php
namespace App\Http\Controllers;
use App\Http\Requests\Admin\AdminOrderQueryRequest;
use App\Http\Requests\Admin\BannerRequest;
use App\Http\Resources\Admin\AdminOrderResource;
use App\Http\Resources\Admin\BannerResource;
use App\Services\Admin\AdminOrderService;
use App\Services\Admin\AdminService;
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

    public function categories(\App\Http\Requests\Admin\CategoryQueryRequest $request): JsonResponse
    {
        $categories = $this->adminService->getCategories($request->validated());
        return ApiResponse::paginated(
            \App\Http\Resources\Admin\AdminCategoryResource::collection($categories),
            $categories,
            'Lấy danh sách danh mục thành công.'
        );
    }

    public function showCategory(int $id): JsonResponse
    {
        $category = $this->adminService->getCategory($id);
        return ApiResponse::success(
            new \App\Http\Resources\Admin\AdminCategoryResource($category),
            'Thao tác thành công',
            200
        );
    }

    public function storeCategory(\App\Http\Requests\Admin\StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->adminService->createCategory($request->validated());
        return ApiResponse::success(
            new \App\Http\Resources\Admin\AdminCategoryResource($category),
            'Tạo danh mục thành công',
            201
        );
    }

    public function updateCategory(\App\Http\Requests\Admin\UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->adminService->updateCategory($id, $request->validated());
        return ApiResponse::success(
            new \App\Http\Resources\Admin\AdminCategoryResource($category),
            'Cập nhật danh mục thành công',
            200
        );
    }

    public function deleteCategory(int $id): JsonResponse
    {
        $this->adminService->deleteCategory($id);
        return ApiResponse::success(
            null,
            'Xóa danh mục thành công',
            200
        );
    }

    public function courses(\App\Http\Requests\Admin\AdminCourseQueryRequest $request): JsonResponse
    {
        $courses = $this->adminService->getCourses($request->validated());
        return ApiResponse::paginated(
            \App\Http\Resources\Admin\AdminCourseResource::collection($courses),
            $courses,
            'Lấy danh sách khóa học thành công.'
        );
    }

    public function showCourse(int $id): JsonResponse
    {
        $course = $this->adminService->getCourse($id);
        return ApiResponse::success(
            new \App\Http\Resources\Admin\AdminCourseResource($course),
            'Thao tác thành công',
            200
        );
    }

    public function updateCourse(\App\Http\Requests\Admin\UpdateAdminCourseRequest $request, int $id): JsonResponse
    {
        $course = $this->adminService->updateCourse($id, $request->validated());
        return ApiResponse::success(
            new \App\Http\Resources\Admin\AdminCourseResource($course),
            'Cập nhật khóa học thành công',
            200
        );
    }

    public function users(\App\Http\Requests\Admin\UserQueryRequest $request): JsonResponse
    {
        $users = $this->adminService->getUsers($request->validated());
        return ApiResponse::paginated(
            \App\Http\Resources\Admin\AdminUserResource::collection($users),
            $users,
            'Lấy danh sách người dùng thành công.'
        );
    }

    public function showUser(int $id): JsonResponse
    {
        $user = $this->adminService->getUser($id);
        return ApiResponse::success(
            new \App\Http\Resources\Admin\AdminUserResource($user),
            'Thao tác thành công',
            200
        );
    }

    public function storeUser(\App\Http\Requests\Admin\StoreUserRequest $request): JsonResponse
    {
        $user = $this->adminService->createUser($request->validated());
        return ApiResponse::success(
            new \App\Http\Resources\Admin\AdminUserResource($user),
            'Tạo người dùng thành công',
            201
        );
    }

    public function updateUser(\App\Http\Requests\Admin\UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->adminService->updateUser($id, $request->validated(), $request->user()->id);
        return ApiResponse::success(
            new \App\Http\Resources\Admin\AdminUserResource($user),
            'Cập nhật người dùng thành công',
            200
        );
    }

    public function deleteUser(Request $request, int $id): JsonResponse
    {
        $this->adminService->deleteUser($id, $request->user()->id);
        return ApiResponse::success(
            null,
            'Xóa người dùng thành công',
            200
        );
    }
}