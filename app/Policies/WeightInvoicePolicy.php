<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Models\User;
use App\Models\WeightInvoice;

class WeightInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invoices.view');
    }

    public function view(User $user, WeightInvoice $invoice): bool
    {
        return $user->can('invoices.view');
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.create');
    }

    public function print(User $user, WeightInvoice $invoice): bool
    {
        return $user->can('invoices.print');
    }

    public function cancel(User $user, WeightInvoice $invoice): bool
    {
        return $user->can('invoices.cancel')
            && $invoice->status === InvoiceStatus::Pending;
    }
}
