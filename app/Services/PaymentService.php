<?php

namespace App\Services;

use App\Enums\CashSessionStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\TicketStatus;
use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\WeightInvoice;
use App\Support\ReferenceGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly CashSessionService $cashSessions,
    ) {}

    /**
     * Record a payment against an invoice. On full settlement the ticket becomes CLOSED.
     */
    public function record(WeightInvoice $invoice, array $data): Payment
    {
        if ($invoice->status !== InvoiceStatus::Pending) {
            throw ValidationException::withMessages([
                'invoice' => "Payments can only be received on pending invoices. This invoice is {$invoice->status->label()}.",
            ]);
        }

        $amount = round((float) $data['amount'], 2);
        $outstanding = $invoice->outstanding;

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount must be greater than zero.',
            ]);
        }

        if ($amount > $outstanding) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'Payment (%s) exceeds the outstanding balance (%s).',
                    number_format($amount, 2),
                    number_format($outstanding, 2),
                ),
            ]);
        }

        $method = $data['payment_method'] instanceof PaymentMethod
            ? $data['payment_method']
            : PaymentMethod::from($data['payment_method']);

        $session = $this->resolveCashSession($method);

        return DB::transaction(function () use ($invoice, $data, $amount, $method, $session): Payment {
            $payment = Payment::create([
                'receipt_number' => ReferenceGenerator::receiptNumber(),
                'invoice_id' => $invoice->id,
                'cash_session_id' => $session?->id,
                'amount' => $amount,
                'payment_method' => $method,
                'reference' => $data['reference'] ?? null,
                'received_by' => Auth::id(),
                'payment_date' => $data['payment_date'] ?? now(),
            ]);

            $invoice->refresh()->load('payments');

            if ($invoice->outstanding <= 0) {
                $invoice->forceFill(['status' => InvoiceStatus::Paid])->save();
                $invoice->ticket->forceFill(['status' => TicketStatus::Closed])->save();
            }

            $this->audit->log('RECEIVE_PAYMENT', 'payments', $payment->id, null, [
                'receipt_number' => $payment->receipt_number,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $amount,
                'method' => $method->value,
                'cash_session_id' => $session?->id,
                'received_by' => Auth::id(),
            ]);

            return $payment;
        });
    }

    /**
     * Allocate one customer payment to pending invoices, oldest first.
     *
     * @return Collection<int, Payment>
     */
    public function recordDemand(Customer $customer, array $data): Collection
    {
        $amount = round((float) $data['amount'], 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount must be greater than zero.',
            ]);
        }

        $method = $data['payment_method'] instanceof PaymentMethod
            ? $data['payment_method']
            : PaymentMethod::from($data['payment_method']);

        $session = $this->resolveCashSession($method);

        return DB::transaction(function () use ($customer, $data, $amount, $method, $session): Collection {
            $invoices = WeightInvoice::query()
                ->where('customer_id', $customer->id)
                ->where('status', InvoiceStatus::Pending)
                ->oldest('created_at')
                ->oldest('id')
                ->lockForUpdate()
                ->get();

            $invoices->load(['payments', 'ticket']);
            $totalOutstanding = round((float) $invoices->sum(
                fn (WeightInvoice $invoice): float => $invoice->outstanding
            ), 2);

            if ($amount > $totalOutstanding) {
                throw ValidationException::withMessages([
                    'amount' => sprintf(
                        'Payment (%s) exceeds the customer demand balance (%s).',
                        number_format($amount, 2),
                        number_format($totalOutstanding, 2),
                    ),
                ]);
            }

            $remaining = $amount;
            $payments = collect();

            foreach ($invoices as $invoice) {
                if ($remaining <= 0) {
                    break;
                }

                $allocation = round(min($remaining, $invoice->outstanding), 2);
                if ($allocation <= 0) {
                    continue;
                }

                $payment = Payment::create([
                    'receipt_number' => ReferenceGenerator::receiptNumber(),
                    'invoice_id' => $invoice->id,
                    'cash_session_id' => $session?->id,
                    'amount' => $allocation,
                    'payment_method' => $method,
                    'reference' => $data['reference'] ?? null,
                    'received_by' => Auth::id(),
                    'payment_date' => $data['payment_date'] ?? now(),
                ]);

                $remaining = round($remaining - $allocation, 2);
                $invoice->refresh()->load('payments');

                if ($invoice->outstanding <= 0) {
                    $invoice->forceFill(['status' => InvoiceStatus::Paid])->save();
                    $invoice->ticket?->forceFill(['status' => TicketStatus::Closed])->save();
                }

                $this->audit->log('RECEIVE_PAYMENT', 'payments', $payment->id, null, [
                    'receipt_number' => $payment->receipt_number,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $allocation,
                    'method' => $method->value,
                    'cash_session_id' => $session?->id,
                    'received_by' => Auth::id(),
                    'demand_payment' => true,
                ]);

                $payments->push($payment);
            }

            $this->audit->log('ALLOCATE_DEMAND_PAYMENT', 'customers', $customer->id, null, [
                'customer' => $customer->name,
                'amount' => $amount,
                'invoice_count' => $payments->count(),
                'payment_ids' => $payments->pluck('id')->all(),
            ]);

            return $payments;
        });
    }

    private function resolveCashSession(PaymentMethod $method): ?CashSession
    {
        if ($method === PaymentMethod::Cash) {
            return $this->cashSessions->ensureOpen();
        }

        return CashSession::query()
            ->open()
            ->where('user_id', Auth::id())
            ->latest('opened_at')
            ->first();
    }
}
