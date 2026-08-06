<x-layouts.app title="Daily Collections">
    <x-page-header title="Daily Collections" subtitle="{{ $date->format('d M Y') }}" />
    <form method="GET" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
        <div><label class="label">Date</label><input type="date" name="date" value="{{ $date->toDateString() }}" class="input"></div>
        <button class="btn-primary" type="submit">Apply</button>
    </form>
    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="card p-4"><p class="label">Total Collections</p><p class="stat-value text-2xl">{{ money($total) }}</p></div>
        <div class="card p-4"><p class="label">Payments</p><p class="stat-value text-2xl">{{ $payments->count() }}</p></div>
        <div class="card p-4"><p class="label">Cash Sessions</p><p class="stat-value text-2xl">{{ $sessions->count() }}</p></div>
    </div>
    <div class="card overflow-hidden">
        <table class="table-industrial">
            <thead><tr><th>Receipt</th><th>Method</th><th>Amount</th><th>Operator</th><th>Session</th><th>Time</th></tr></thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="font-mono">{{ $payment->receipt_number }}</td>
                        <td>{{ $payment->payment_method->label() }}</td>
                        <td>{{ money($payment->amount) }}</td>
                        <td>{{ $payment->receiver?->name }}</td>
                        <td>{{ $payment->cash_session_id ? '#'.$payment->cash_session_id : '—' }}</td>
                        <td>{{ $payment->payment_date->format('H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-steel-400">No collections.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
