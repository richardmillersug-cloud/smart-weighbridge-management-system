<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\WeightInvoice;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DemandingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', WeightInvoice::class);

        $search = trim((string) $request->string('search'));

        $customers = Customer::query()
            ->select('customers.*')
            ->selectSub(
                WeightInvoice::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('weight_invoices.customer_id', 'customers.id')
                    ->where('status', InvoiceStatus::Pending)
                    ->whereNull('weight_invoices.deleted_at'),
                'demanded_count'
            )
            ->selectSub(
                WeightInvoice::query()
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('weight_invoices.customer_id', 'customers.id')
                    ->where('status', InvoiceStatus::Pending)
                    ->whereNull('weight_invoices.deleted_at'),
                'demanded_amount'
            )
            ->selectSub(
                DB::table('payments')
                    ->join('weight_invoices', 'payments.invoice_id', '=', 'weight_invoices.id')
                    ->selectRaw('COALESCE(SUM(payments.amount), 0)')
                    ->whereColumn('weight_invoices.customer_id', 'customers.id')
                    ->where('weight_invoices.status', InvoiceStatus::Pending->value)
                    ->whereNull('weight_invoices.deleted_at'),
                'paid_on_demands'
            )
            ->whereHas('invoices', fn ($q) => $q->where('status', InvoiceStatus::Pending))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('demanded_amount')
            ->paginate(20)
            ->withQueryString();

        $totals = WeightInvoice::query()
            ->outstanding()
            ->with('payments')
            ->get();

        $grandInvoices = $totals->count();
        $grandAmount = (float) $totals->sum('amount');
        $grandPaid = (float) $totals->sum(fn ($invoice) => $invoice->amount_paid);
        $grandBalance = max(0, $grandAmount - $grandPaid);

        return view('demandings.index', [
            'customers' => $customers,
            'grandInvoices' => $grandInvoices,
            'grandAmount' => $grandAmount,
            'grandPaid' => $grandPaid,
            'grandBalance' => $grandBalance,
        ]);
    }

    public function show(Customer $customer): View
    {
        $this->authorize('viewAny', WeightInvoice::class);

        $invoices = WeightInvoice::query()
            ->with(['ticket.vehicle', 'ticket.product', 'payments', 'creator'])
            ->where('customer_id', $customer->id)
            ->where('status', InvoiceStatus::Pending)
            ->latest()
            ->get();

        $totalAmount = (float) $invoices->sum('amount');
        $totalPaid = (float) $invoices->sum(fn ($invoice) => $invoice->amount_paid);
        $totalBalance = max(0, $totalAmount - $totalPaid);

        return view('demandings.show', compact(
            'customer',
            'invoices',
            'totalAmount',
            'totalPaid',
            'totalBalance',
        ));
    }

    public function createPayment(Customer $customer): View
    {
        $this->authorize('create', Payment::class);

        $invoices = WeightInvoice::query()
            ->with(['ticket', 'payments'])
            ->where('customer_id', $customer->id)
            ->where('status', InvoiceStatus::Pending)
            ->oldest('created_at')
            ->get();

        abort_if($invoices->isEmpty(), 404, 'This customer has no outstanding demanded invoices.');

        $totalBalance = (float) $invoices->sum(fn ($invoice) => $invoice->outstanding);

        return view('demandings.pay', [
            'customer' => $customer,
            'invoices' => $invoices,
            'totalBalance' => $totalBalance,
            'methods' => PaymentMethod::cases(),
        ]);
    }

    public function storePayment(
        Request $request,
        Customer $customer,
        PaymentService $payments,
    ): RedirectResponse {
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['nullable', 'date', 'before_or_equal:now'],
        ]);

        $allocations = $payments->recordDemand($customer, $validated);

        return redirect()
            ->route('demandings.show', $customer)
            ->with(
                'success',
                sprintf(
                    '%s allocated across %d invoice(s), oldest first.',
                    money((float) $validated['amount']),
                    $allocations->count(),
                ),
            );
    }
}
