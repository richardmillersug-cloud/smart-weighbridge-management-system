<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Models\WeightInvoice;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Payment::class);

        return redirect()->route('invoices.index', array_merge(
            $request->query(),
            ['tab' => 'payments'],
        ));
    }

    public function create(WeightInvoice $invoice): View
    {
        $this->authorize('create', Payment::class);

        abort_unless($invoice->status === InvoiceStatus::Pending, 403, 'This invoice is not payable.');

        $invoice->load(['customer', 'ticket', 'payments']);

        return view('payments.create', [
            'invoice' => $invoice,
            'methods' => PaymentMethod::cases(),
        ]);
    }

    public function store(StorePaymentRequest $request, PaymentService $payments): RedirectResponse
    {
        $invoice = WeightInvoice::findOrFail($request->validated('invoice_id'));

        $payment = $payments->record($invoice, $request->validated());

        return redirect()
            ->route('payments.receipt', $payment)
            ->with('success', "Payment {$payment->receipt_number} recorded.");
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['invoice.customer', 'invoice.ticket', 'receiver']);

        return view('payments.show', compact('payment'));
    }

    public function receipt(Payment $payment, \App\Services\AuditService $audit): View
    {
        $this->authorize('view', $payment);

        $payment->load(['invoice.customer', 'invoice.ticket.vehicle', 'receiver']);

        $audit->log('RECEIPT_PRINTED', 'payments', $payment->id, null, [
            'receipt_number' => $payment->receipt_number,
        ]);

        return view('payments.receipt', compact('payment'));
    }
}
