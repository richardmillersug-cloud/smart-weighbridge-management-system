<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.edit');
    }

    public function disable(User $user, User $model): bool
    {
        return $user->can('users.disable') && $user->isNot($model);
    }

    public function assignRoles(User $user, User $model): bool
    {
        return $user->can('users.assign-roles');
    }
}
