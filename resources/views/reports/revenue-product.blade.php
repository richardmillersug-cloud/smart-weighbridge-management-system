<x-layouts.app title="Revenue by Product">
    <x-page-header title="Revenue by Product" subtitle="{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}" />
    <form method="GET" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <div><label class="label">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="input"></div>
        <div><label class="label">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="input"></div>
        <button class="btn-primary" type="submit">Apply</button>
    </form>
    <div class="card overflow-hidden">
        <table class="table-industrial">
            <thead><tr><th>Product</th><th>Invoices</th><th>Actual kg</th><th>Revenue</th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $products[$row->product_id]->name ?? '—' }}</td>
                        <td>{{ $row->invoices }}</td>
                        <td>{{ number_format((float) $row->actual_total, 2) }}</td>
                        <td>{{ money($row->revenue) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-8 text-center text-steel-400">No revenue data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
