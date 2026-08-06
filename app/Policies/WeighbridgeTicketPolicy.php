<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeighbridgeTicket;

class WeighbridgeTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tickets.view');
    }

    public function view(User $user, WeighbridgeTicket $ticket): bool
    {
        return $user->can('tickets.view');
    }

    public function create(User $user): bool
    {
        return $user->can('tickets.create');
    }

    /**
     * Weights may only be captured on tickets still in the weighing flow.
     * Captured weights are immutable - there is deliberately no ability
     * to edit them afterwards.
     */
    public function captureWeight(User $user, WeighbridgeTicket $ticket): bool
    {
        return $user->can('tickets.capture-weight')
            && ! $ticket->status->isFinal()
            && ($ticket->status->canCaptureGross() || $ticket->status->canCaptureTare());
    }

    /**
     * Only in-progress tickets may be cancelled; completed, invoiced and
     * paid tickets are permanent records.
     */
    public function cancel(User $user, WeighbridgeTicket $ticket): bool
    {
        return $user->can('tickets.cancel') && $ticket->status->canBeCancelled();
    }
}
