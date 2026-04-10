<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Measurement;
use App\Models\User;

class MeasurementPolicy
{
    public function viewAny(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    public function view(User $user, Measurement $measurement): bool
    {
        return $measurement->user_id === $user->id;
    }

    public function create(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    public function update(User $user, Measurement $measurement): bool
    {
        return $measurement->user_id === $user->id;
    }

    public function delete(User $user, Measurement $measurement): bool
    {
        return $measurement->user_id === $user->id;
    }
}
