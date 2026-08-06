<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeighbridgeStation;

class WeighbridgeStationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('stations.view');
    }

    public function view(User $user, WeighbridgeStation $station): bool
    {
        return $user->can('stations.view');
    }

    public function create(User $user): bool
    {
        return $user->can('stations.create');
    }

    public function update(User $user, WeighbridgeStation $station): bool
    {
        return $user->can('stations.edit');
    }

    public function delete(User $user, WeighbridgeStation $station): bool
    {
        return $user->can('stations.delete');
    }
}
