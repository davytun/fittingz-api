<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    public function create(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    public function update(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    public function delete(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
