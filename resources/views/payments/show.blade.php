<x-layouts.app :title="'Payment '.$payment->receipt_number">
    <x-page-header :title="$payment->receipt_number" subtitle="Payment details">
        <x-slot:actions>
            <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="btn-secondary">Print Receipt</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card max-w-3xl">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-5 px-6 py-6 md:grid-cols-3">
            <div>
                <dt class="label mb-0.5">Amount</dt>
                <dd class="font-mono text-lg font-bold text-emerald-400">{{ money($payment->amount) }}</dd>
            </div>
            <div>
                <dt class="label mb-0.5">Method</dt>
                <dd class="text-sm font-semibold text-slate-100">{{ $payment->payment_method->label() }}</dd>
            </div>
            <div>
                <dt class="label mb-0.5">Reference</dt>
                <dd class="font-mono text-sm text-slate-100">{{ $payment->reference ?? '—' }}</dd>
            </div>
            <div>
                <dt class="label mb-0.5">Invoice</dt>
                <dd>
                    <a href="{{ route('invoices.show', $payment->invoice) }}" class="font-mono text-sm font-semibold text-brand-400 hover:text-brand-300">{{ $payment->invoice->invoice_number }}</a>
                </dd>
            </div>
            <div>
                <dt class="label mb-0.5">Customer</dt>
                <dd class="text-sm font-semibold text-slate-100">{{ $payment->invoice->customer->name }}</dd>
            </div>
            <div>
                <dt class="label mb-0.5">Ticket</dt>
                <dd>
                    <a href="{{ route('tickets.show', $payment->invoice->ticket) }}" class="font-mono text-sm text-steel-300 hover:text-slate-100">{{ $payment->invoice->ticket->ticket_number }}</a>
                </dd>
            </div>
            <div>
                <dt class="label mb-0.5">Received By</dt>
                <dd class="text-sm font-semibold text-slate-100">{{ $payment->receiver->name }}</dd>
            </div>
            <div>
                <dt class="label mb-0.5">Payment Date</dt>
                <dd class="text-sm font-semibold text-slate-100">{{ $payment->payment_date->format('d M Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</x-layouts.app>
