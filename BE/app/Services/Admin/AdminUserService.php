<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Admin\AdminUserRepository;

final class AdminUserService
{
    public function __construct(private readonly AdminUserRepository $repo, private readonly AdminNotificationService $notifications) {}
    public function paginate(array $filters)
    {
        return $this->repo->paginate($filters);
    }
    public function show(User $user): User
    {
        return $user;
    }
    public function block(User $user, User $admin): User
    {
        $user->update(['status' => 'blocked']);
        $this->notifications->audit($admin, 'user.block', $user);
        return $user->fresh();
    }
    public function unblock(User $user, User $admin): User
    {
        $user->update(['status' => 'active']);
        $this->notifications->audit($admin, 'user.unblock', $user);
        return $user->fresh();
    }
    public function approveInstructor(User $user, User $admin): User
    {
        $user->update(['role' => 'instructor', 'status' => 'active']);
        $this->notifications->audit($admin, 'user.approve_instructor', $user);
        return $user->fresh();
    }
}
