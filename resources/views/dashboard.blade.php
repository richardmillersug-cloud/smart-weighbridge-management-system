<x-layouts.app title="Dashboard">

    {{-- ===== Stat cards ===== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Today's Tickets" :value="$stats['tickets_today']"
                     :sub="$stats['open_tickets'].' still on the floor'" accent="sky">
            <x-slot:icon>
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-12-2.25h16.5A1.5 1.5 0 0 0 22.5 14.25v-1.5a2.25 2.25 0 0 1 0-4.5v-1.5A1.5 1.5 0 0 0 21 5.25H4.5A1.5 1.5 0 0 0 3 6.75v1.5a2.25 2.25 0 0 1 0 4.5v1.5a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Completed Weighings" :value="$stats['completed_today']"
                     :sub="'Net total '.number_format($stats['net_weight_today'] / 1000, 2).' t'" accent="emerald">
            <x-slot:icon>
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Invoices Today" :value="$stats['invoices_today']"
                     :sub="money($stats['invoice_amount_today'])" accent="brand">
            <x-slot:icon>
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Payments Collected" :value="money($stats['payments_today'])"
                     :sub="$stats['outstanding_invoices'].' invoices outstanding ('.money($stats['outstanding_amount']).')'" accent="red">
            <x-slot:icon>
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    {{-- ===== Charts ===== --}}
    <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="card-header">
                <h3 class="card-title">Weighing Activity — Last 7 Days</h3>
            </div>
            <div class="p-5">
                <canvas id="weighingTrendChart" height="110"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Collections — Last 7 Days</h3>
            </div>
            <div class="p-5">
                <canvas id="paymentsChart" height="230"></canvas>
            </div>
        </div>
    </div>

    {{-- ===== Recent activity ===== --}}
    <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="card-header">
                <h3 class="card-title">Recent Tickets</h3>
                @can('tickets.view')
                    <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-brand-400 uppercase hover:text-brand-300">View all &rarr;</a>
                @endcan
            </div>
            <div class="overflow-x-auto">
                <table class="table-industrial">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th class="text-right">Net (kg)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                </td>
                                <td class="font-semibold text-slate-100">{{ $ticket->vehicle->plate_number }}</td>
                                <td>{{ $ticket->customer->name }}</td>
                                <td>{{ $ticket->product->name }}</td>
                                <td class="text-right font-mono">{{ $ticket->net_weight !== null ? number_format((float) $ticket->net_weight, 2) : '—' }}</td>
                                <td><x-status-badge :status="$ticket->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state message="No tickets recorded yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Latest Payments</h3>
                @can('payments.view')
                    <a href="{{ route('invoices.index', ['tab' => 'payments']) }}" class="text-xs font-semibold text-brand-400 uppercase hover:text-brand-300">View all &rarr;</a>
                @endcan
            </div>
            <ul class="divide-y divide-steel-800">
                @forelse ($recentPayments as $payment)
                    <li class="flex items-center justify-between gap-3 px-5 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-100">{{ $payment->invoice->customer->name }}</p>
                            <p class="font-mono text-[11px] text-steel-400">{{ $payment->receipt_number }} · {{ $payment->payment_date->format('d M H:i') }}</p>
                        </div>
                        <p class="font-mono text-sm font-bold text-emerald-400">{{ money($payment->amount) }}</p>
                    </li>
                @empty
                    <li><x-empty-state message="No payments recorded yet." /></li>
                @endforelse
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trend = @json($trend);

            new Chart(document.getElementById('weighingTrendChart'), {
                data: {
                    labels: trend.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Tickets',
                            data: trend.tickets,
                            backgroundColor: 'rgba(235, 159, 20, 0.55)',
                            borderColor: '#eb9f14',
                            borderWidth: 1,
                            borderRadius: 4,
                            yAxisID: 'y',
                        },
                        {
                            type: 'line',
                            label: 'Net Weight (t)',
                            data: trend.net,
                            borderColor: '#38bdf8',
                            backgroundColor: 'rgba(56, 189, 248, 0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            yAxisID: 'y1',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: { position: 'left', beginAtZero: true, ticks: { precision: 0 } },
                        y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } },
                    },
                },
            });

            new Chart(document.getElementById('paymentsChart'), {
                type: 'bar',
                data: {
                    labels: trend.labels,
                    datasets: [{
                        label: 'Collections',
                        data: trend.payments,
                        backgroundColor: 'rgba(52, 211, 153, 0.5)',
                        borderColor: '#34d399',
                        borderWidth: 1,
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { display: false } },
                },
            });
        });
    </script>
</x-layouts.app>
