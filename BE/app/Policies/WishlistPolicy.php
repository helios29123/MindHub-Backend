<?php
namespace App\Policies;
use App\Models\User;
use App\Models\Wishlist;
final class WishlistPolicy
{
    public function manage(User $user, Wishlist $wishlist): bool
    {
        return (int) $user->id === (int) $wishlist->user_id;
    }
}