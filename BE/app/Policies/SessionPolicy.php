<?php
namespace App\Policies;
use App\Models\Session;
use App\Models\User;
final class SessionPolicy
{
    public function view(User $user, Session $session): bool
    {
        return (int) $session->user_id === (int) $user->id;
    }
    public function revoke(User $user, Session $session): bool
    {
        return (int) $session->user_id === (int) $user->id;
    }
}