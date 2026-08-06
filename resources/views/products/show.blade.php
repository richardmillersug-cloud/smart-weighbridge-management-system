<x-layouts.app :title="$product->name">
    <x-page-header :title="$product->name" :subtitle="$product->description">
        <x-slot:actions>
            @can('products.edit')
                <a href="{{ route('products.edit', $product) }}" class="btn-secondary">Edit</a>
            @endcan
            @can('products.delete')
                <form method="POST" action="{{ route('products.destroy', $product) }}"
                      onsubmit="return confirm('Archive this product?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">Archive</button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Weighing History</h3></div>
        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr><th>Ticket</th><th>Vehicle</th><th>Customer</th><th class="text-right">Net (kg)</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $ticket->ticket_number }}</a></td>
                            <td class="font-mono">{{ $ticket->vehicle->plate_number }}</td>
                            <td>{{ $ticket->customer->name }}</td>
                            <td class="text-right font-mono font-bold">{{ $ticket->net_weight !== null ? number_format((float) $ticket->net_weight, 2) : '—' }}</td>
                            <td><x-status-badge :status="$ticket->status" /></td>
                            <td class="text-xs text-steel-400">{{ $ticket->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state message="No weighing history for this product." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4">{{ $tickets->links() }}</div>
    </div>
</x-layouts.app>
