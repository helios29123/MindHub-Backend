<?php
namespace App\Http\Controllers;
use App\Http\Requests\Wishlist\StoreWishlistRequest;
use App\Http\Resources\Wishlist\WishlistResource;
use App\Services\Wishlist\WishlistService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
final class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlistService
    ) {
    }
    public function store(StoreWishlistRequest $request): JsonResponse
    {
        $wishlist = $this->wishlistService->addCourseToWishlist(
            $request->user(),
            (int) $request->validated('course_id')
        );
        return ApiResponse::success(
            new WishlistResource($wishlist),
            'Thêm khóa học vào danh sách yêu thích thành công.',
            201
        );
    }
}