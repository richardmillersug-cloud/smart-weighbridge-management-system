<?php

namespace App\Policies;

use App\Enums\CashSessionStatus;
use App\Models\CashSession;
use App\Models\User;

class CashSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cash-sessions.view');
    }

    public function view(User $user, CashSession $session): bool
    {
        return $user->can('cash-sessions.view')
            && ($user->id === $session->user_id || $user->can('settings.manage'));
    }

    public function open(User $user): bool
    {
        return $user->can('cash-sessions.open');
    }

    public function close(User $user, CashSession $session): bool
    {
        return $user->can('cash-sessions.close')
            && $session->status === CashSessionStatus::Open
            && ($user->id === $session->user_id || $user->can('settings.manage'));
    }
}
