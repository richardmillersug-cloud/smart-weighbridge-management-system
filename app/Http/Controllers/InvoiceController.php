<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Payment;
use App\Models\WeighbridgeTicket;
use App\Models\WeightInvoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $canInvoices = $request->user()->can('invoices.view');
        $canPayments = $request->user()->can('payments.view');

        abort_unless($canInvoices || $canPayments, 403);

        $tab = $request->string('tab')->toString();
        if (! in_array($tab, ['invoices', 'payments'], true)) {
            $tab = $canInvoices ? 'invoices' : 'payments';
        }

        if ($tab === 'invoices' && ! $canInvoices) {
            $tab = 'payments';
        }
        if ($tab === 'payments' && ! $canPayments) {
            $tab = 'invoices';
        }

        $invoices = null;
        $payments = null;
        $statuses = InvoiceStatus::cases();
        $methods = PaymentMethod::cases();

        if ($tab === 'invoices') {
            $this->authorize('viewAny', WeightInvoice::class);

            $invoices = WeightInvoice::query()
                ->with(['customer', 'ticket', 'creator', 'payments'])
                ->when($request->filled('search'), function ($query) use ($request): void {
                    $search = $request->string('search');
                    $query->where(fn ($q) => $q
                        ->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('ticket', fn ($t) => $t->where('ticket_number', 'like', "%{$search}%")));
                })
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
                ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
                ->latest()
                ->paginate(15)
                ->withQueryString();
        } else {
            $this->authorize('viewAny', Payment::class);

            $payments = Payment::query()
                ->with(['invoice.customer', 'invoice.ticket', 'receiver'])
                ->when($request->filled('search'), function ($query) use ($request): void {
                    $search = $request->string('search');
                    $query->where(fn ($q) => $q
                        ->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', "%{$search}%")));
                })
                ->when($request->filled('method'), fn ($q) => $q->where('payment_method', $request->string('method')))
                ->when($request->filled('from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->date('from')))
                ->when($request->filled('to'), fn ($q) => $q->whereDate('payment_date', '<=', $request->date('to')))
                ->latest('payment_date')
                ->paginate(15)
                ->withQueryString();
        }

        return view('billing.index', [
            'tab' => $tab,
            'invoices' => $invoices,
            'payments' => $payments,
            'statuses' => $statuses,
            'methods' => $methods,
            'canInvoices' => $canInvoices,
            'canPayments' => $canPayments,
        ]);
    }

    /**
     * Rate entry form for invoicing a completed ticket.
     */
    public function createForTicket(WeighbridgeTicket $ticket): View
    {
        $this->authorize('create', WeightInvoice::class);

        abort_unless($ticket->status->canBeInvoiced(), 403, 'Only completed tickets can be invoiced.');

        $ticket->load(['customer', 'vehicle', 'product']);

        return view('invoices.create', [
            'ticket' => $ticket,
        ]);
    }

    public function store(StoreInvoiceRequest $request, InvoiceService $invoices): RedirectResponse
    {
        $ticket = WeighbridgeTicket::findOrFail($request->validated('ticket_id'));

        $invoice = $invoices->generateForTicket($ticket, (float) $request->validated('amount'));

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} generated.");
    }

    public function show(WeightInvoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['customer', 'ticket.vehicle', 'ticket.driver', 'ticket.product', 'creator', 'payments.receiver']);

        return view('invoices.show', compact('invoice'));
    }

    public function print(WeightInvoice $invoice): View
    {
        $this->authorize('print', $invoice);

        $invoice->load(['customer', 'ticket.vehicle', 'ticket.product', 'creator']);

        return view('invoices.print', compact('invoice'));
    }

    public function cancel(Request $request, WeightInvoice $invoice, InvoiceService $invoices): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $invoices->cancel($invoice, $validated['reason']);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} cancelled.");
    }
}
