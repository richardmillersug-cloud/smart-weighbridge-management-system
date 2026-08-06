<x-layouts.app title="Billing">
    <x-page-header title="Billing" subtitle="Invoices and payment collections">
        <x-slot:actions>
            <div class="flex rounded-lg border border-steel-700 bg-steel-800/40 p-0.5">
                @if ($canInvoices)
                    <a href="{{ route('invoices.index', ['tab' => 'invoices']) }}"
                       class="rounded-md px-4 py-1.5 text-xs font-semibold uppercase tracking-wider {{ $tab === 'invoices' ? 'bg-brand-500/20 text-brand-400' : 'text-steel-400 hover:text-slate-100' }}">
                        Invoices
                    </a>
                @endif
                @if ($canPayments)
                    <a href="{{ route('invoices.index', ['tab' => 'payments']) }}"
                       class="rounded-md px-4 py-1.5 text-xs font-semibold uppercase tracking-wider {{ $tab === 'payments' ? 'bg-brand-500/20 text-brand-400' : 'text-steel-400 hover:text-slate-100' }}">
                        Payments
                    </a>
                @endif
            </div>
        </x-slot:actions>
    </x-page-header>

    @if ($tab === 'invoices')
        <div class="card">
            <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
                <input type="hidden" name="tab" value="invoices">
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
                            <th>Ticket / Invoice</th>
                            <th>Customer</th>
                            <th class="text-right">Net (kg)</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Balance</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        <a href="{{ route('tickets.show', $invoice->ticket) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $invoice->ticket->ticket_number }}</a>
                                        <a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-[11px] text-steel-400 hover:text-slate-100">{{ $invoice->invoice_number }}</a>
                                    </div>
                                </td>
                                <td class="max-w-40 truncate">{{ $invoice->customer->name }}</td>
                                <td class="text-right font-mono">{{ number_format((float) $invoice->net_weight, 2) }}</td>
                                <td class="text-right font-mono font-bold text-slate-100">{{ money($invoice->amount) }}</td>
                                <td class="text-right font-mono {{ $invoice->outstanding > 0 ? 'text-amber-400' : 'text-emerald-400' }}">{{ money($invoice->outstanding) }}</td>
                                <td><x-status-badge :status="$invoice->status" /></td>
                                <td class="text-xs text-steel-400">{{ $invoice->created_at->format('d M H:i') }}</td>
                                <td class="text-right">
                                    @if ($invoice->status === \App\Enums\InvoiceStatus::Pending)
                                        @can('payments.receive')
                                            <a href="{{ route('payments.create', $invoice) }}" class="btn-primary px-3 py-1.5 text-xs">Pay</a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><x-empty-state message="No invoices match your filters." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4">{{ $invoices->links() }}</div>
        </div>
    @else
        <div class="card">
            <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
                <input type="hidden" name="tab" value="payments">
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
                            <th>Ticket / Invoice</th>
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
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        @if ($payment->invoice->ticket)
                                            <a href="{{ route('tickets.show', $payment->invoice->ticket) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $payment->invoice->ticket->ticket_number }}</a>
                                        @endif
                                        <a href="{{ route('invoices.show', $payment->invoice) }}" class="font-mono text-[11px] text-steel-400 hover:text-slate-100">{{ $payment->invoice->invoice_number }}</a>
                                    </div>
                                </td>
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
    @endif
</x-layouts.app>
