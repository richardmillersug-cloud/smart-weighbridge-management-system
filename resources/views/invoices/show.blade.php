<x-layouts.app :title="'Invoice '.$invoice->invoice_number">
    <x-page-header :title="$invoice->invoice_number" subtitle="Weight invoice details">
        <x-slot:actions>
            <x-status-badge :status="$invoice->status" class="text-sm" />

            @can('print', $invoice)
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn-secondary">Print Invoice</a>
            @endcan

            @if ($invoice->status === \App\Enums\InvoiceStatus::Pending)
                @can('payments.receive')
                    <a href="{{ route('payments.create', $invoice) }}" class="btn-primary">Receive Payment</a>
                @endcan
            @endif

            @can('cancel', $invoice)
                <div x-data="{ open: false }">
                    <button @click="open = true" class="btn-danger">Cancel Invoice</button>
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                        <div @click.outside="open = false" class="card w-full max-w-md p-6">
                            <h3 class="card-title mb-4">Cancel {{ $invoice->invoice_number }}</h3>
                            <form method="POST" action="{{ route('invoices.cancel', $invoice) }}">
                                @csrf
                                <label class="label" for="reason">Reason for cancellation</label>
                                <textarea id="reason" name="reason" rows="3" required class="input"></textarea>
                                <div class="mt-4 flex justify-end gap-3">
                                    <button type="button" @click="open = false" class="btn-ghost">Keep Invoice</button>
                                    <button type="submit" class="btn-danger">Confirm Cancellation</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="card-header"><h3 class="card-title">Invoice Details</h3></div>

            <dl class="grid grid-cols-2 gap-x-6 gap-y-5 px-6 py-5 md:grid-cols-3">
                <div>
                    <dt class="label mb-0.5">Customer</dt>
                    <dd class="text-sm font-semibold text-slate-100">
                        <a href="{{ route('customers.show', $invoice->customer) }}" class="hover:text-brand-400">{{ $invoice->customer->name }}</a>
                    </dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Ticket</dt>
                    <dd class="font-mono text-sm font-semibold text-brand-400">
                        <a href="{{ route('tickets.show', $invoice->ticket) }}" class="hover:text-brand-300">{{ $invoice->ticket->ticket_number }}</a>
                    </dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Vehicle</dt>
                    <dd class="font-mono text-sm font-semibold text-slate-100">{{ $invoice->ticket->vehicle->plate_number }}</dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Product</dt>
                    <dd class="text-sm font-semibold text-slate-100">{{ $invoice->ticket->product->name }}</dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Issued By</dt>
                    <dd class="text-sm font-semibold text-slate-100">{{ $invoice->creator->name }}</dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Issued At</dt>
                    <dd class="text-sm font-semibold text-slate-100">{{ $invoice->created_at->format('d M Y H:i') }}</dd>
                </div>
                @if ($invoice->cancel_reason)
                    <div class="col-span-2 md:col-span-3">
                        <dt class="label mb-0.5 text-red-400">Cancellation Reason</dt>
                        <dd class="text-sm text-red-300">{{ $invoice->cancel_reason }}</dd>
                    </div>
                @endif
            </dl>

            <div class="grid grid-cols-1 gap-4 border-t border-steel-700/60 px-6 py-5 md:grid-cols-3">
                <div class="rounded-lg border border-steel-700 bg-carbon-900 p-4 text-center">
                    <p class="font-display text-[10px] font-semibold tracking-widest text-steel-400 uppercase">Net Weight</p>
                    <p class="mt-1 font-mono text-xl font-bold text-slate-100">{{ number_format((float) $invoice->net_weight, 2) }} <span class="text-xs">kg</span></p>
                </div>
                <div class="rounded-lg border border-brand-500/40 bg-brand-500/5 p-4 text-center">
                    <p class="font-display text-[10px] font-semibold tracking-widest text-brand-500 uppercase">Amount</p>
                    <p class="mt-1 font-mono text-xl font-bold text-brand-400">{{ money($invoice->amount) }}</p>
                </div>
                <div class="rounded-lg border {{ $invoice->outstanding > 0 ? 'border-amber-500/40 bg-amber-500/5' : 'border-emerald-500/40 bg-emerald-500/5' }} p-4 text-center">
                    <p class="font-display text-[10px] font-semibold tracking-widest {{ $invoice->outstanding > 0 ? 'text-amber-500' : 'text-emerald-500' }} uppercase">Balance</p>
                    <p class="mt-1 font-mono text-xl font-bold {{ $invoice->outstanding > 0 ? 'text-amber-400' : 'text-emerald-400' }}">{{ money($invoice->outstanding) }}</p>
                </div>
            </div>
        </div>

        <div class="card self-start">
            <div class="card-header"><h3 class="card-title">Payments</h3></div>
            @if ($invoice->payments->isNotEmpty())
                <ul class="divide-y divide-steel-800">
                    @foreach ($invoice->payments as $payment)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('payments.show', $payment) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">{{ $payment->receipt_number }}</a>
                                <span class="font-mono text-sm font-bold text-emerald-400">{{ money($payment->amount) }}</span>
                            </div>
                            <p class="mt-1 text-xs text-steel-400">
                                {{ $payment->payment_method->label() }} · {{ $payment->payment_date->format('d M Y H:i') }} · {{ $payment->receiver->name }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-empty-state message="No payments recorded for this invoice." />
            @endif
        </div>
    </div>
</x-layouts.app>
