<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\TicketStatus;
use App\Models\Payment;
use App\Models\WeighbridgeTicket;
use App\Models\WeightInvoice;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = today();

        $stats = [
            'tickets_today' => WeighbridgeTicket::whereDate('created_at', $today)->count(),
            'completed_today' => WeighbridgeTicket::whereDate('created_at', $today)
                ->whereNotNull('net_weight')
                ->count(),
            'net_weight_today' => (float) WeighbridgeTicket::whereDate('created_at', $today)
                ->sum('net_weight'),
            'invoices_today' => WeightInvoice::whereDate('created_at', $today)
                ->where('status', '!=', InvoiceStatus::Cancelled)
                ->count(),
            'invoice_amount_today' => (float) WeightInvoice::whereDate('created_at', $today)
                ->where('status', '!=', InvoiceStatus::Cancelled)
                ->sum('amount'),
            'payments_today' => (float) Payment::whereDate('payment_date', $today)->sum('amount'),
            'open_tickets' => WeighbridgeTicket::whereDate('created_at', $today)
                ->open()
                ->count(),
            'outstanding_invoices' => WeightInvoice::where('status', InvoiceStatus::Pending)->count(),
            'outstanding_amount' => (float) WeightInvoice::where('status', InvoiceStatus::Pending)->sum('amount')
                - (float) Payment::whereHas('invoice', fn ($q) => $q->where('status', InvoiceStatus::Pending))->sum('amount'),
        ];

        $trend = $this->weeklyTrend();

        $recentTickets = WeighbridgeTicket::with(['customer', 'vehicle', 'product'])
            ->latest()
            ->limit(8)
            ->get();

        $recentPayments = Payment::with(['invoice.customer', 'receiver'])
            ->latest('payment_date')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'trend', 'recentTickets', 'recentPayments'));
    }

    /**
     * Ticket counts, net weight and payments for the last 7 days,
     * shaped for Chart.js.
     *
     * @return array{labels: array, tickets: array, net: array, payments: array}
     */
    private function weeklyTrend(): array
    {
        $from = now()->subDays(6)->startOfDay();

        $tickets = WeighbridgeTicket::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total, COALESCE(SUM(net_weight), 0) as net')
            ->where('created_at', '>=', $from)
            ->groupBy('day')
            ->pluck('total', 'day');

        $net = WeighbridgeTicket::query()
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(net_weight), 0) as net')
            ->where('created_at', '>=', $from)
            ->groupBy('day')
            ->pluck('net', 'day');

        $payments = Payment::query()
            ->selectRaw('DATE(payment_date) as day, COALESCE(SUM(amount), 0) as total')
            ->where('payment_date', '>=', $from)
            ->groupBy('day')
            ->pluck('total', 'day');

        $trend = ['labels' => [], 'tickets' => [], 'net' => [], 'payments' => []];

        foreach (range(6, 0) as $daysAgo) {
            $date = now()->subDays($daysAgo);
            $key = $date->format('Y-m-d');

            $trend['labels'][] = $date->format('D d M');
            $trend['tickets'][] = (int) ($tickets[$key] ?? 0);
            $trend['net'][] = round(((float) ($net[$key] ?? 0)) / 1000, 2); // tonnes
            $trend['payments'][] = (float) ($payments[$key] ?? 0);
        }

        return $trend;
    }
}
