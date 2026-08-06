<x-layouts.app :title="$title">
    <x-page-header :title="$title" subtitle="{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}" />

    <form method="GET" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <div><label class="label">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="input"></div>
        <div><label class="label">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="input"></div>
        <button class="btn-primary" type="submit">Apply</button>
    </form>

    <div class="card overflow-hidden">
        <table class="table-industrial">
            <thead><tr><th>Name</th><th>Tickets</th><th>Net kg</th><th>Actual kg</th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $label($row) }}</td>
                        <td>{{ $row->tickets }}</td>
                        <td>{{ number_format((float) $row->net_total, 2) }}</td>
                        <td>{{ number_format((float) $row->actual_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-8 text-center text-steel-400">No data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
