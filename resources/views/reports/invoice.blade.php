<x-layouts.app title="Invoice Report">
    <x-page-header title="Invoice Report" :subtitle="$from->format('d M Y').' — '.$to->format('d M Y')">
        <x-slot:actions>
            <button onclick="window.print()" class="btn-secondary no-print">Print</button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="no-print mb-6 flex flex-wrap items-end gap-3">
        <div class="w-44">
            <label class="label" for="from">From</label>
            <input id="from" type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input">
        </div>
        <div class="w-44">
            <label class="label" for="to">To</label>
            <input id="to" type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input">
        </div>
        <button type="submit" class="btn-primary">Run Report</button>
    </form>

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-5">
        <x-stat-card label="Invoices" :value="$summary['count']" />
        <x-stat-card label="Total Billed" :value="money($summary['amount'])" accent="brand" />
        <x-stat-card label="Paid" :value="money($summary['paid'])" accent="emerald" />
        <x-stat-card label="Pending" :value="money($summary['pending'])" accent="sky" />
        <x-stat-card label="Cancelled" :value="$summary['cancelled']" accent="red" />
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice</th>
                        <th>Ticket</th>
                        <th>Customer</th>
                        <th>Issued By</th>
                        <th class="text-right">Net (kg)</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="font-mono text-xs">{{ $invoice->created_at->format('d M H:i') }}</td>
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $invoice->invoice_number }}</a></td>
                            <td class="font-mono text-xs">{{ $invoice->ticket->ticket_number }}</td>
                            <td class="max-w-40 truncate">{{ $invoice->customer->name }}</td>
                            <td>{{ $invoice->creator->name }}</td>
                            <td class="text-right font-mono">{{ number_format((float) $invoice->net_weight, 2) }}</td>
                            <td class="text-right font-mono font-bold">{{ money($invoice->amount) }}</td>
                            <td><x-status-badge :status="$invoice->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state message="No invoices issued in this period." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
