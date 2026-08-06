<x-layouts.app title="Cash Session">
    <x-page-header title="Cash Session" subtitle="Opened {{ $cashSession->opened_at->format('d M Y H:i') }}" />

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="card p-4"><p class="label">Operator</p><p class="font-semibold">{{ $cashSession->user?->name }}</p></div>
        <div class="card p-4"><p class="label">Opening</p><p class="font-semibold">{{ money($cashSession->opening_cash) }}</p></div>
        <div class="card p-4"><p class="label">Expected</p><p class="font-semibold">{{ $cashSession->expected_cash !== null ? money($cashSession->expected_cash) : money((float)$cashSession->opening_cash + $cashSession->cashCollected()) }}</p></div>
        <div class="card p-4"><p class="label">Status</p><p class="font-semibold">{{ $cashSession->status->label() }}</p></div>
    </div>

    <div class="card overflow-hidden">
        <div class="card-header"><h3 class="card-title">Payments in session</h3></div>
        <table class="table-industrial">
            <thead><tr><th>Receipt</th><th>Invoice</th><th>Method</th><th>Amount</th><th>Date</th></tr></thead>
            <tbody>
                @forelse ($cashSession->payments as $payment)
                    <tr>
                        <td class="font-mono">{{ $payment->receipt_number }}</td>
                        <td>{{ $payment->invoice?->invoice_number }}</td>
                        <td>{{ $payment->payment_method->label() }}</td>
                        <td>{{ money($payment->amount) }}</td>
                        <td>{{ $payment->payment_date->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-steel-400">No payments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
