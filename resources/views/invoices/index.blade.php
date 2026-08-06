<x-layouts.app title="Invoices">
    <x-page-header title="Weight Invoices" subtitle="Billing for completed weighings" />

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-full sm:w-56">
                <label class="label" for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Invoice, ticket or customer">
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
                        <th>Invoice</th>
                        <th>Ticket</th>
                        <th>Customer</th>
                        <th class="text-right">Net (kg)</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Balance</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $invoice->invoice_number }}</a></td>
                            <td><a href="{{ route('tickets.show', $invoice->ticket) }}" class="font-mono text-xs text-steel-300 hover:text-slate-100">{{ $invoice->ticket->ticket_number }}</a></td>
                            <td class="max-w-40 truncate">{{ $invoice->customer->name }}</td>
                            <td class="text-right font-mono">{{ number_format((float) $invoice->net_weight, 2) }}</td>
                            <td class="text-right font-mono font-bold text-slate-100">{{ money($invoice->amount) }}</td>
                            <td class="text-right font-mono {{ $invoice->outstanding > 0 ? 'text-amber-400' : 'text-emerald-400' }}">{{ money($invoice->outstanding) }}</td>
                            <td><x-status-badge :status="$invoice->status" /></td>
                            <td class="text-xs text-steel-400">{{ $invoice->created_at->format('d M H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state message="No invoices match your filters." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $invoices->links() }}</div>
    </div>
</x-layouts.app>
