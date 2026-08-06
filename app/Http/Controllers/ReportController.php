<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\TicketStatus;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\User;
use App\Models\WeighbridgeTicket;
use App\Models\WeightInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Daily Weighing Report - every ticket for a chosen day with totals.
     */
    public function daily(Request $request): View
    {
        $date = $request->filled('date') ? Carbon::parse($request->string('date')) : today();

        $tickets = WeighbridgeTicket::with(['customer', 'vehicle', 'driver', 'product', 'creator'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at')
            ->get();

        $summary = [
            'total' => $tickets->count(),
            'completed' => $tickets->whereNotNull('net_weight')->count(),
            'cancelled' => $tickets->where('status', TicketStatus::Cancelled)->count(),
            'gross' => (float) $tickets->sum('gross_weight'),
            'tare' => (float) $tickets->sum('tare_weight'),
            'net' => (float) $tickets->sum('net_weight'),
        ];

        return view('reports.daily', compact('tickets', 'summary', 'date'));
    }

    /**
     * Operator Report - activity per bridge handler over a date range.
     */
    public function operator(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $operators = User::query()
            ->withCount([
                'ticketsCreated as tickets_count' => fn ($q) => $q->whereBetween('created_at', [$from, $to]),
                'ticketsCompleted as completed_count' => fn ($q) => $q->whereBetween('updated_at', [$from, $to]),
                'invoicesCreated as invoices_count' => fn ($q) => $q->whereBetween('created_at', [$from, $to]),
                'paymentsReceived as payments_count' => fn ($q) => $q->whereBetween('payment_date', [$from, $to]),
            ])
            ->withSum([
                'ticketsCompleted as net_weight_sum' => fn ($q) => $q->whereBetween('updated_at', [$from, $to]),
            ], 'net_weight')
            ->withSum([
                'paymentsReceived as payments_sum' => fn ($q) => $q->whereBetween('payment_date', [$from, $to]),
            ], 'amount')
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => $user->tickets_count > 0
                || $user->invoices_count > 0
                || $user->payments_count > 0);

        return view('reports.operator', compact('operators', 'from', 'to'));
    }

    /**
     * Invoice Report - invoices issued over a date range.
     */
    public function invoice(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $invoices = WeightInvoice::with(['customer', 'ticket', 'creator', 'payments'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $summary = [
            'count' => $invoices->count(),
            'amount' => (float) $invoices->where('status', '!=', InvoiceStatus::Cancelled)->sum('amount'),
            'paid' => (float) $invoices->where('status', InvoiceStatus::Paid)->sum('amount'),
            'pending' => (float) $invoices->where('status', InvoiceStatus::Pending)->sum('amount'),
            'cancelled' => $invoices->where('status', InvoiceStatus::Cancelled)->count(),
        ];

        return view('reports.invoice', compact('invoices', 'summary', 'from', 'to'));
    }

    /**
     * Payment Report - collections over a date range, grouped by method.
     */
    public function payment(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $payments = Payment::with(['invoice.customer', 'receiver'])
            ->whereBetween('payment_date', [$from, $to])
            ->orderBy('payment_date')
            ->get();

        $byMethod = $payments
            ->groupBy(fn (Payment $payment) => $payment->payment_method->value)
            ->map(fn ($group) => [
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ]);

        return view('reports.payment', [
            'payments' => $payments,
            'byMethod' => $byMethod,
            'total' => (float) $payments->sum('amount'),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Audit Report - user activity over a date range.
     */
    public function audit(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $logs = AuditLog::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->string('module')))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name']);
        $modules = AuditLog::query()->distinct()->orderBy('module')->pluck('module');

        return view('reports.audit', compact('logs', 'users', 'modules', 'from', 'to'));
    }

    public function productSummary(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = WeighbridgeTicket::query()
            ->selectRaw('product_id, COUNT(*) as tickets, SUM(net_weight) as net_total, SUM(actual_weight) as actual_total')
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('net_weight')
            ->groupBy('product_id')
            ->with('product')
            ->get();

        return view('reports.summary', [
            'title' => 'Product Summary',
            'rows' => $rows,
            'label' => fn ($row) => $row->product?->name ?? '—',
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function customerSummary(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = WeighbridgeTicket::query()
            ->selectRaw('customer_id, COUNT(*) as tickets, SUM(net_weight) as net_total, SUM(actual_weight) as actual_total')
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('net_weight')
            ->groupBy('customer_id')
            ->with('customer')
            ->get();

        return view('reports.summary', [
            'title' => 'Customer Summary',
            'rows' => $rows,
            'label' => fn ($row) => $row->customer?->name ?? '—',
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function vehicleSummary(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = WeighbridgeTicket::query()
            ->selectRaw('vehicle_id, COUNT(*) as tickets, SUM(net_weight) as net_total, SUM(actual_weight) as actual_total')
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('net_weight')
            ->groupBy('vehicle_id')
            ->with('vehicle')
            ->get();

        return view('reports.summary', [
            'title' => 'Vehicle Summary',
            'rows' => $rows,
            'label' => fn ($row) => $row->vehicle?->plate_number ?? '—',
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function outstandingInvoices(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $invoices = WeightInvoice::with(['customer', 'ticket', 'payments'])
            ->where('status', InvoiceStatus::Pending)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        return view('reports.invoice-list', [
            'title' => 'Outstanding Invoices',
            'invoices' => $invoices,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function paidInvoices(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $invoices = WeightInvoice::with(['customer', 'ticket', 'payments'])
            ->where('status', InvoiceStatus::Paid)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        return view('reports.invoice-list', [
            'title' => 'Paid Invoices',
            'invoices' => $invoices,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function revenueByProduct(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = WeightInvoice::query()
            ->selectRaw('weighbridge_tickets.product_id, COUNT(weight_invoices.id) as invoices, SUM(weight_invoices.amount) as revenue, SUM(weight_invoices.actual_weight) as actual_total')
            ->join('weighbridge_tickets', 'weighbridge_tickets.id', '=', 'weight_invoices.ticket_id')
            ->where('weight_invoices.status', '!=', InvoiceStatus::Cancelled->value)
            ->whereBetween('weight_invoices.created_at', [$from, $to])
            ->groupBy('weighbridge_tickets.product_id')
            ->get();

        $products = \App\Models\Product::whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id');

        return view('reports.revenue-product', compact('rows', 'products', 'from', 'to'));
    }

    public function dailyCollections(Request $request): View
    {
        $date = $request->filled('date') ? Carbon::parse($request->string('date')) : today();

        $payments = Payment::with(['invoice.customer', 'receiver', 'cashSession'])
            ->whereDate('payment_date', $date)
            ->orderBy('payment_date')
            ->get();

        $sessions = \App\Models\CashSession::with('user')
            ->whereDate('opened_at', $date)
            ->get();

        return view('reports.collections', [
            'payments' => $payments,
            'sessions' => $sessions,
            'total' => (float) $payments->sum('amount'),
            'date' => $date,
        ]);
    }

    public function cancelledTickets(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $tickets = WeighbridgeTicket::with(['customer', 'vehicle', 'creator'])
            ->where('status', TicketStatus::Cancelled)
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        return view('reports.cancelled', compact('tickets', 'from', 'to'));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->string('to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }
}
