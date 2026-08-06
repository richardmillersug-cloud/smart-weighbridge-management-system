<x-layouts.app title="Payment Report">
    <x-page-header title="Payment Report" :subtitle="$from->format('d M Y').' — '.$to->format('d M Y')">
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

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-stat-card label="Total Collected" :value="money($total)" accent="emerald" />
        @foreach ($byMethod as $method => $data)
            <x-stat-card :label="\App\Enums\PaymentMethod::from($method)->label()"
                         :value="money($data['total'])"
                         :sub="$data['count'].' payments'" />
        @endforeach
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Receipt</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Received By</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="font-mono text-xs">{{ $payment->payment_date->format('d M H:i') }}</td>
                            <td><a href="{{ route('payments.show', $payment) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $payment->receipt_number }}</a></td>
                            <td class="font-mono text-xs">{{ $payment->invoice->invoice_number }}</td>
                            <td class="max-w-40 truncate">{{ $payment->invoice->customer->name }}</td>
                            <td>{{ $payment->payment_method->label() }}</td>
                            <td class="font-mono text-xs">{{ $payment->reference ?? '—' }}</td>
                            <td>{{ $payment->receiver->name }}</td>
                            <td class="text-right font-mono font-bold text-emerald-400">{{ money($payment->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state message="No payments collected in this period." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
