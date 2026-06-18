<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return (int) $order->user_id === (int) $user->id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->role === User::ROLE_LEARNER
            && (int) $order->user_id === (int) $user->id;
    }    public function retryPayment(User $user, Order $order): bool
    {
        return $user->role === 'learner'
            && (int) $order->user_id === (int) $user->id;
    }
}