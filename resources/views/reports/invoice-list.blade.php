<x-layouts.app :title="$title">
    <x-page-header :title="$title" subtitle="{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}" />
    <form method="GET" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <div><label class="label">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="input"></div>
        <div><label class="label">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="input"></div>
        <button class="btn-primary" type="submit">Apply</button>
    </form>
    <div class="card overflow-hidden">
        <table class="table-industrial">
            <thead><tr><th>Invoice</th><th>Customer</th><th>Ticket</th><th>Actual kg</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="font-mono">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->customer?->name }}</td>
                        <td>{{ $invoice->ticket?->ticket_number }}</td>
                        <td>{{ number_format((float) ($invoice->actual_weight ?? $invoice->net_weight), 2) }}</td>
                        <td>{{ money($invoice->amount) }}</td>
                        <td>{{ $invoice->status->label() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-steel-400">No invoices.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
