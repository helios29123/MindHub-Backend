<?php
namespace App\Services\Wishlist;
use App\Exceptions\BusinessException;
use App\Models\User;
use App\Models\Wishlist;
use App\Repositories\Wishlist\WishlistRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
final class WishlistService
{
    public function __construct(
        private readonly WishlistRepository $wishlistRepository
    ) {
    }
    public function addCourseToWishlist(User $user, int $courseId): Wishlist
    {
        return DB::transaction(function () use ($user, $courseId): Wishlist {
            $course = $this->wishlistRepository->findPublishedCourse($courseId);
            if ($course === null) {
                throw new ModelNotFoundException();
            }
            if ($this->wishlistRepository->exists((int) $user->id, (int) $course->id)) {
                throw new BusinessException(
                    'Khóa học đã có trong danh sách yêu thích.',
                    409
                );
            }
            $wishlist = $this->wishlistRepository->create(
                (int) $user->id,
                (int) $course->id
            );
            return $wishlist->load('course');
        });
    }
    public function removeCourseFromWishlist(User $user, int $courseId): array
    {
        return DB::transaction(function () use ($user, $courseId): array {
            $course = $this->wishlistRepository->findCourse($courseId);
            if ($course === null) {
                throw new BusinessException(
                    'Không tìm thấy khóa học trong danh sách yêu thích.',
                    404
                );
            }
            $wishlist = $this->wishlistRepository->findByUserAndCourse(
                (int) $user->id,
                (int) $course->id
            );
            if ($wishlist === null) {
                throw new BusinessException(
                    'Không tìm thấy khóa học trong danh sách yêu thích.',
                    404
                );
            }
            $this->wishlistRepository->delete($wishlist);
            return [
                'course_id' => (int) $course->id,
                'is_wishlisted' => false,
            ];
        });
    }
}