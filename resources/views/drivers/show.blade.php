<x-layouts.app :title="$driver->name">
    <x-page-header :title="$driver->name" :subtitle="'Licence '.$driver->license_number">
        <x-slot:actions>
            @can('drivers.edit')
                <a href="{{ route('drivers.edit', $driver) }}" class="btn-secondary">Edit</a>
            @endcan
            @can('drivers.delete')
                <form method="POST" action="{{ route('drivers.destroy', $driver) }}"
                      onsubmit="return confirm('Archive this driver?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">Archive</button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="card p-4">
            <p class="label mb-0.5">Phone</p>
            <p class="text-sm font-semibold text-slate-100">{{ $driver->phone ?? '—' }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Licence</p>
            <p class="font-mono text-sm font-semibold text-slate-100">{{ $driver->license_number }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Status</p>
            <x-status-badge :status="$driver->status" />
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Registered</p>
            <p class="text-sm font-semibold text-slate-100">{{ $driver->created_at->format('d M Y') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Weighing History</h3></div>
        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr><th>Ticket</th><th>Vehicle</th><th>Customer</th><th>Product</th><th class="text-right">Net (kg)</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $ticket->ticket_number }}</a></td>
                            <td class="font-mono">{{ $ticket->vehicle->plate_number }}</td>
                            <td>{{ $ticket->customer->name }}</td>
                            <td>{{ $ticket->product->name }}</td>
                            <td class="text-right font-mono font-bold">{{ $ticket->net_weight !== null ? number_format((float) $ticket->net_weight, 2) : '—' }}</td>
                            <td><x-status-badge :status="$ticket->status" /></td>
                            <td class="text-xs text-steel-400">{{ $ticket->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state message="No weighing history for this driver." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4">{{ $tickets->links() }}</div>
    </div>
</x-layouts.app>
