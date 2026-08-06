<x-layouts.app :title="$vehicle->plate_number">
    <x-page-header :title="$vehicle->plate_number" subtitle="Vehicle history">
        <x-slot:actions>
            @can('vehicles.edit')
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn-secondary">Edit</a>
            @endcan
            @can('vehicles.delete')
                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}"
                      onsubmit="return confirm('Archive this vehicle?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">Archive</button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="card p-4">
            <p class="label mb-0.5">Owner</p>
            <p class="text-sm font-semibold text-slate-100">{{ $vehicle->owner_name ?? '—' }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Capacity</p>
            <p class="text-sm font-semibold text-slate-100">{{ $vehicle->capacity !== null ? kg($vehicle->capacity, 0) : '—' }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Status</p>
            <x-status-badge :status="$vehicle->status" />
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Registered</p>
            <p class="text-sm font-semibold text-slate-100">{{ $vehicle->created_at->format('d M Y') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Weighing History</h3></div>
        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr><th>Ticket</th><th>Customer</th><th>Product</th><th class="text-right">Gross (kg)</th><th class="text-right">Tare (kg)</th><th class="text-right">Net (kg)</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $ticket->ticket_number }}</a></td>
                            <td>{{ $ticket->customer->name }}</td>
                            <td>{{ $ticket->product->name }}</td>
                            <td class="text-right font-mono">{{ $ticket->gross_weight !== null ? number_format((float) $ticket->gross_weight, 2) : '—' }}</td>
                            <td class="text-right font-mono">{{ $ticket->tare_weight !== null ? number_format((float) $ticket->tare_weight, 2) : '—' }}</td>
                            <td class="text-right font-mono font-bold">{{ $ticket->net_weight !== null ? number_format((float) $ticket->net_weight, 2) : '—' }}</td>
                            <td><x-status-badge :status="$ticket->status" /></td>
                            <td class="text-xs text-steel-400">{{ $ticket->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state message="No weighing history for this vehicle." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4">{{ $tickets->links() }}</div>
    </div>
</x-layouts.app>
