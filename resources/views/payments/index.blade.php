<x-layouts.app title="Payments">
    <x-page-header title="Payments" subtitle="Collections and outstanding invoices" />

    @if ($outstandingInvoices->isNotEmpty())
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">Outstanding Invoices</h3>
                <a href="{{ route('invoices.index', ['status' => 'PENDING']) }}" class="text-xs font-semibold text-brand-400 uppercase hover:text-brand-300">View all &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table-industrial">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Balance</th>
                            <th>Date</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($outstandingInvoices as $invoice)
                            <tr>
                                <td><a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $invoice->invoice_number }}</a></td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td class="text-right font-mono">{{ money($invoice->amount) }}</td>
                                <td class="text-right font-mono font-bold text-amber-400">{{ money($invoice->outstanding) }}</td>
                                <td class="text-xs text-steel-400">{{ $invoice->created_at->format('d M H:i') }}</td>
                                <td class="text-right">
                                    @can('payments.receive')
                                        <a href="{{ route('payments.create', $invoice) }}" class="btn-primary px-3 py-1.5 text-xs">Receive</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h3 class="card-title">Payment History</h3></div>

        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-full sm:w-56">
                <label class="label" for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Receipt, invoice or reference">
            </div>
            <div class="w-44">
                <label class="label" for="method">Method</label>
                <select id="method" name="method" class="input">
                    <option value="">All</option>
                    @foreach ($methods as $method)
                        <option value="{{ $method->value }}" @selected(request('method') === $method->value)>{{ $method->label() }}</option>
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
                        <th>Receipt</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th class="text-right">Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Received By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td><a href="{{ route('payments.show', $payment) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $payment->receipt_number }}</a></td>
                            <td><a href="{{ route('invoices.show', $payment->invoice) }}" class="font-mono text-xs text-steel-300 hover:text-slate-100">{{ $payment->invoice->invoice_number }}</a></td>
                            <td class="max-w-40 truncate">{{ $payment->invoice->customer->name }}</td>
                            <td class="text-right font-mono font-bold text-emerald-400">{{ money($payment->amount) }}</td>
                            <td>{{ $payment->payment_method->label() }}</td>
                            <td class="font-mono text-xs">{{ $payment->reference ?? '—' }}</td>
                            <td>{{ $payment->receiver->name }}</td>
                            <td class="text-xs text-steel-400">{{ $payment->payment_date->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state message="No payments match your filters." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $payments->links() }}</div>
    </div>
</x-layouts.app>
