<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\TicketStatus;
use App\Models\WeighbridgeTicket;
use App\Models\WeightInvoice;
use App\Support\ReferenceGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Generate an invoice using the amount payable selected for the vehicle size.
     */
    public function generateForTicket(WeighbridgeTicket $ticket, float $amount): WeightInvoice
    {
        if (! $ticket->status->canBeInvoiced()) {
            throw ValidationException::withMessages([
                'ticket' => "Only completed tickets can be invoiced. This ticket is {$ticket->status->label()}.",
            ]);
        }

        if ($ticket->invoice()->exists()) {
            throw ValidationException::withMessages([
                'ticket' => 'An invoice has already been generated for this ticket.',
            ]);
        }

        $billable = (float) ($ticket->actual_weight ?? $ticket->net_weight);

        if ($billable <= 0) {
            throw ValidationException::withMessages([
                'ticket' => 'Cannot invoice a ticket with zero actual weight.',
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount payable must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($ticket, $amount, $billable): WeightInvoice {
            $invoice = WeightInvoice::create([
                'invoice_number' => ReferenceGenerator::invoiceNumber(),
                'ticket_id' => $ticket->id,
                'customer_id' => $ticket->customer_id,
                'net_weight' => (float) $ticket->net_weight,
                'actual_weight' => $billable,
                'rate' => 0,
                'amount' => round($amount, 2),
                'status' => InvoiceStatus::Pending,
                'created_by' => Auth::id(),
            ]);

            $ticket->forceFill([
                'status' => TicketStatus::Invoiced,
                'unit_price' => 0,
                'total_amount' => $invoice->amount,
            ])->save();

            $this->audit->log('GENERATE_INVOICE', 'weight_invoices', $invoice->id, null, [
                'invoice_number' => $invoice->invoice_number,
                'ticket_number' => $ticket->ticket_number,
                'actual_weight' => $billable,
                'amount' => $invoice->amount,
            ]);

            return $invoice;
        });
    }

    public function cancel(WeightInvoice $invoice, string $reason): WeightInvoice
    {
        if ($invoice->status !== InvoiceStatus::Pending) {
            throw ValidationException::withMessages([
                'invoice' => "A {$invoice->status->label()} invoice cannot be cancelled.",
            ]);
        }

        if ($invoice->payments()->exists()) {
            throw ValidationException::withMessages([
                'invoice' => 'This invoice already has recorded payments and cannot be cancelled.',
            ]);
        }

        return DB::transaction(function () use ($invoice, $reason): WeightInvoice {
            $invoice->forceFill([
                'status' => InvoiceStatus::Cancelled,
                'cancel_reason' => $reason,
            ])->save();

            $invoice->ticket->forceFill(['status' => TicketStatus::Completed])->save();

            $this->audit->log('CANCEL', 'weight_invoices', $invoice->id, null, ['reason' => $reason]);

            return $invoice;
        });
    }
}
