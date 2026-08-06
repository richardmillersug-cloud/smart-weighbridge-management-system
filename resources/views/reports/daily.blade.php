<x-layouts.app title="Daily Weighing Report">
    <x-page-header title="Daily Weighing Report" :subtitle="$date->format('l, d F Y')">
        <x-slot:actions>
            <button onclick="window.print()" class="btn-secondary no-print">Print</button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="no-print mb-6 flex items-end gap-3">
        <div class="w-48">
            <label class="label" for="date">Report date</label>
            <input id="date" type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="input">
        </div>
        <button type="submit" class="btn-primary">Run Report</button>
    </form>

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-6">
        <x-stat-card label="Tickets" :value="$summary['total']" />
        <x-stat-card label="Completed" :value="$summary['completed']" accent="emerald" />
        <x-stat-card label="Cancelled" :value="$summary['cancelled']" accent="red" />
        <x-stat-card label="Gross (t)" :value="number_format($summary['gross'] / 1000, 2)" />
        <x-stat-card label="Tare (t)" :value="number_format($summary['tare'] / 1000, 2)" />
        <x-stat-card label="Net (t)" :value="number_format($summary['net'] / 1000, 2)" accent="brand" />
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Ticket</th>
                        <th>Vehicle</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Operator</th>
                        <th class="text-right">Gross (kg)</th>
                        <th class="text-right">Tare (kg)</th>
                        <th class="text-right">Net (kg)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td class="font-mono text-xs">{{ $ticket->created_at->format('H:i') }}</td>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $ticket->ticket_number }}</a></td>
                            <td class="font-mono">{{ $ticket->vehicle->plate_number }}</td>
                            <td class="max-w-36 truncate">{{ $ticket->customer->name }}</td>
                            <td>{{ $ticket->product->name }}</td>
                            <td>{{ $ticket->creator->name }}</td>
                            <td class="text-right font-mono">{{ $ticket->gross_weight !== null ? number_format((float) $ticket->gross_weight, 2) : '—' }}</td>
                            <td class="text-right font-mono">{{ $ticket->tare_weight !== null ? number_format((float) $ticket->tare_weight, 2) : '—' }}</td>
                            <td class="text-right font-mono font-bold">{{ $ticket->net_weight !== null ? number_format((float) $ticket->net_weight, 2) : '—' }}</td>
                            <td><x-status-badge :status="$ticket->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><x-empty-state message="No weighings recorded on this day." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
