<x-layouts.app title="Weight Tickets">
    <x-page-header title="Weight Tickets" subtitle="All weighbridge transactions">
        <x-slot:actions>
            @can('tickets.create')
                <a href="{{ route('weighbridge') }}" class="btn-primary">Open Weighing Station</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-full sm:w-56">
                <label class="label" for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Ticket, plate or customer">
            </div>
            <div class="w-44">
                <label class="label" for="status">Status</label>
                <select id="status" name="status" class="input">
                    <option value="">All</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="label" for="from">From</label>
                <input id="from" type="date" name="from" value="{{ request('from') }}" class="input">
            </div>
            <div class="w-40">
                <label class="label" for="to">To</label>
                <input id="to" type="date" name="to" value="{{ request('to') }}" class="input">
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Ticket / Invoice</th>
                        <th>Vehicle</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th class="text-right">Gross (kg)</th>
                        <th class="text-right">Tare (kg)</th>
                        <th class="text-right">Net (kg)</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $ticket->ticket_number }}</a>
                                    @if ($ticket->invoice)
                                        <a href="{{ route('invoices.show', $ticket->invoice) }}" class="font-mono text-[11px] text-steel-400 hover:text-slate-100">{{ $ticket->invoice->invoice_number }}</a>
                                    @else
                                        <span class="text-[11px] text-steel-500">No invoice</span>
                                    @endif
                                </div>
                            </td>
                            <td class="font-mono font-bold text-slate-100">{{ $ticket->vehicle->plate_number }}</td>
                            <td class="max-w-40 truncate">{{ $ticket->customer->name }}</td>
                            <td>{{ $ticket->product->name }}</td>
                            <td class="text-right font-mono">{{ $ticket->gross_weight !== null ? number_format((float) $ticket->gross_weight, 2) : '—' }}</td>
                            <td class="text-right font-mono">{{ $ticket->tare_weight !== null ? number_format((float) $ticket->tare_weight, 2) : '—' }}</td>
                            <td class="text-right font-mono font-bold">{{ $ticket->net_weight !== null ? number_format((float) $ticket->net_weight, 2) : '—' }}</td>
                            <td><x-status-badge :status="$ticket->status" /></td>
                            <td class="text-xs text-steel-400">{{ $ticket->created_at->format('d M H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><x-empty-state message="No tickets match your filters." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $tickets->links() }}</div>
    </div>
</x-layouts.app>
