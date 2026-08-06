<x-layouts.app title="Cancelled Tickets">
    <x-page-header title="Cancelled Tickets" subtitle="{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}" />
    <form method="GET" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <div><label class="label">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="input"></div>
        <div><label class="label">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="input"></div>
        <button class="btn-primary" type="submit">Apply</button>
    </form>
    <div class="card overflow-hidden">
        <table class="table-industrial">
            <thead><tr><th>Ticket</th><th>Truck</th><th>Customer</th><th>Operator</th><th>Reason</th><th>When</th></tr></thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td class="font-mono">{{ $ticket->ticket_number }}</td>
                        <td>{{ $ticket->vehicle?->plate_number }}</td>
                        <td>{{ $ticket->customer?->name }}</td>
                        <td>{{ $ticket->creator?->name }}</td>
                        <td>{{ $ticket->cancel_reason }}</td>
                        <td>{{ $ticket->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-steel-400">No cancelled tickets.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
