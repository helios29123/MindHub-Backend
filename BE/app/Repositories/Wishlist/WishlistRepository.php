<?php
namespace App\Repositories\Wishlist;
use App\Models\Course;
use App\Models\Wishlist;
final class WishlistRepository
{
    public function findCourse(int $courseId): ?Course
    {
        return Course::query()
            ->whereKey($courseId)
            ->whereNull('deleted_at')
            ->first();
    }
    public function findPublishedCourse(int $courseId): ?Course
    {
        return Course::query()
            ->whereKey($courseId)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->first();
    }
    public function exists(int $userId, int $courseId): bool
    {
        return Wishlist::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
    }
    public function findByUserAndCourse(int $userId, int $courseId): ?Wishlist
    {
        return Wishlist::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
    }
    public function create(int $userId, int $courseId): Wishlist
    {
        return Wishlist::query()->create([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);
    }
    public function delete(Wishlist $wishlist): bool
    {
        return (bool) $wishlist->delete();
    }
}