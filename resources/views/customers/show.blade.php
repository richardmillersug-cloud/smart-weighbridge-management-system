<x-layouts.app :title="$customer->name">
    <x-page-header :title="$customer->name" :subtitle="'Customer '.$customer->customer_code">
        <x-slot:actions>
            @can('customers.edit')
                <a href="{{ route('customers.edit', $customer) }}" class="btn-secondary">Edit</a>
            @endcan
            @can('customers.delete')
                <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                      onsubmit="return confirm('Archive this customer?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">Archive</button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="card p-4">
            <p class="label mb-0.5">Phone</p>
            <p class="text-sm font-semibold text-slate-100">{{ $customer->phone ?? '—' }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Address</p>
            <p class="truncate text-sm font-semibold text-slate-100">{{ $customer->address ?? '—' }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Status</p>
            <x-status-badge :status="$customer->status" />
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Registered</p>
            <p class="text-sm font-semibold text-slate-100">{{ $customer->created_at->format('d M Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Weighing History</h3></div>
            <div class="overflow-x-auto">
                <table class="table-industrial">
                    <thead>
                        <tr><th>Ticket</th><th>Vehicle</th><th>Product</th><th class="text-right">Net (kg)</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td><a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $ticket->ticket_number }}</a></td>
                                <td>{{ $ticket->vehicle->plate_number }}</td>
                                <td>{{ $ticket->product->name }}</td>
                                <td class="text-right font-mono">{{ $ticket->net_weight !== null ? number_format((float) $ticket->net_weight, 2) : '—' }}</td>
                                <td><x-status-badge :status="$ticket->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty-state message="No weighing history." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4">{{ $tickets->links() }}</div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Invoices</h3></div>
            <div class="overflow-x-auto">
                <table class="table-industrial">
                    <thead>
                        <tr><th>Invoice</th><th>Ticket</th><th class="text-right">Amount</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td><a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $invoice->invoice_number }}</a></td>
                                <td class="font-mono text-xs">{{ $invoice->ticket->ticket_number }}</td>
                                <td class="text-right font-mono">{{ money($invoice->amount) }}</td>
                                <td><x-status-badge :status="$invoice->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state message="No invoices yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4">{{ $invoices->links() }}</div>
        </div>
    </div>
</x-layouts.app>
