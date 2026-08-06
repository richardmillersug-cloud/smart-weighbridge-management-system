<x-layouts.app :title="'Ticket '.$ticket->ticket_number">
    <x-page-header :title="$ticket->ticket_number" subtitle="Weighbridge ticket details">
        <x-slot:actions>
            <x-status-badge :status="$ticket->status" class="text-sm" />
            <a href="{{ route('tickets.print', $ticket) }}" target="_blank" class="btn-secondary">Print Ticket</a>

            @if ($ticket->status->canBeInvoiced())
                @can('invoices.create')
                    <a href="{{ route('invoices.create', $ticket) }}" class="btn-primary">Generate Invoice</a>
                @endcan
            @endif

            @can('cancel', $ticket)
                <div x-data="{ open: false }">
                    <button @click="open = true" class="btn-danger">Cancel Ticket</button>
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                        <div @click.outside="open = false" class="card w-full max-w-md p-6">
                            <h3 class="card-title mb-4">Cancel {{ $ticket->ticket_number }}</h3>
                            <form method="POST" action="{{ route('tickets.cancel', $ticket) }}">
                                @csrf
                                <label class="label" for="reason">Reason for cancellation</label>
                                <textarea id="reason" name="reason" rows="3" required class="input"></textarea>
                                <div class="mt-4 flex justify-end gap-3">
                                    <button type="button" @click="open = false" class="btn-ghost">Keep Ticket</button>
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
            <div class="card-header"><h3 class="card-title">Transaction</h3></div>

            <dl class="grid grid-cols-2 gap-x-6 gap-y-5 px-6 py-5 md:grid-cols-3">
                <div>
                    <dt class="label mb-0.5">Customer</dt>
                    <dd class="text-sm font-semibold text-slate-100">
                        <a href="{{ route('customers.show', $ticket->customer) }}" class="hover:text-brand-400">{{ $ticket->customer->name }}</a>
                    </dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Vehicle</dt>
                    <dd class="font-mono text-sm font-semibold text-slate-100">
                        <a href="{{ route('vehicles.show', $ticket->vehicle) }}" class="hover:text-brand-400">{{ $ticket->vehicle->plate_number }}</a>
                    </dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Driver</dt>
                    <dd class="text-sm font-semibold text-slate-100">
                        <a href="{{ route('drivers.show', $ticket->driver) }}" class="hover:text-brand-400">{{ $ticket->driver->name }}</a>
                    </dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Product</dt>
                    <dd class="text-sm font-semibold text-slate-100">{{ $ticket->product->name }}</dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Created By</dt>
                    <dd class="text-sm font-semibold text-slate-100">{{ $ticket->creator->name }}</dd>
                </div>
                <div>
                    <dt class="label mb-0.5">Completed By</dt>
                    <dd class="text-sm font-semibold text-slate-100">{{ $ticket->completer?->name ?? '—' }}</dd>
                </div>
                @if ($ticket->remarks)
                    <div class="col-span-2 md:col-span-3">
                        <dt class="label mb-0.5">Remarks</dt>
                        <dd class="text-sm text-slate-300">{{ $ticket->remarks }}</dd>
                    </div>
                @endif
                @if ($ticket->cancel_reason)
                    <div class="col-span-2 md:col-span-3">
                        <dt class="label mb-0.5 text-red-400">Cancellation Reason</dt>
                        <dd class="text-sm text-red-300">{{ $ticket->cancel_reason }}</dd>
                    </div>
                @endif
            </dl>

            <div class="grid grid-cols-1 gap-4 border-t border-steel-700/60 px-6 py-5 md:grid-cols-3">
                <div class="rounded-lg border border-steel-700 bg-carbon-900 p-4 text-center">
                    <p class="font-display text-[10px] font-semibold tracking-widest text-steel-400 uppercase">Gross Weight</p>
                    <p class="mt-1 font-mono text-2xl font-bold text-slate-100">{{ $ticket->gross_weight !== null ? number_format((float) $ticket->gross_weight, 2) : '—' }} <span class="text-xs">kg</span></p>
                    <p class="mt-1 text-[10px] text-steel-400">{{ $ticket->gross_captured_at?->format('d M Y H:i:s') ?? 'Not captured' }}</p>
                </div>
                <div class="rounded-lg border border-steel-700 bg-carbon-900 p-4 text-center">
                    <p class="font-display text-[10px] font-semibold tracking-widest text-steel-400 uppercase">Tare Weight</p>
                    <p class="mt-1 font-mono text-2xl font-bold text-slate-100">{{ $ticket->tare_weight !== null ? number_format((float) $ticket->tare_weight, 2) : '—' }} <span class="text-xs">kg</span></p>
                    <p class="mt-1 text-[10px] text-steel-400">{{ $ticket->tare_captured_at?->format('d M Y H:i:s') ?? 'Not captured' }}</p>
                </div>
                <div class="rounded-lg border border-brand-500/40 bg-brand-500/5 p-4 text-center">
                    <p class="font-display text-[10px] font-semibold tracking-widest text-brand-500 uppercase">Net Weight</p>
                    <p class="mt-1 font-mono text-2xl font-bold text-brand-400">{{ $ticket->net_weight !== null ? number_format((float) $ticket->net_weight, 2) : '—' }} <span class="text-xs">kg</span></p>
                </div>
            </div>
        </div>

        <div class="card self-start">
            <div class="card-header"><h3 class="card-title">Billing</h3></div>
            @if ($ticket->invoice)
                <div class="space-y-4 px-6 py-5">
                    <div>
                        <p class="label mb-0.5">Invoice</p>
                        <a href="{{ route('invoices.show', $ticket->invoice) }}" class="font-mono text-sm font-bold text-brand-400 hover:text-brand-300">
                            {{ $ticket->invoice->invoice_number }}
                        </a>
                    </div>
                    <div>
                        <p class="label mb-0.5">Amount</p>
                        <p class="font-mono text-lg font-bold text-slate-100">{{ money($ticket->invoice->amount) }}</p>
                    </div>
                    <div>
                        <p class="label mb-0.5">Status</p>
                        <x-status-badge :status="$ticket->invoice->status" />
                    </div>
                    @if ($ticket->invoice->payments->isNotEmpty())
                        <div>
                            <p class="label mb-1">Payments</p>
                            <ul class="space-y-1.5">
                                @foreach ($ticket->invoice->payments as $payment)
                                    <li class="flex items-center justify-between text-sm">
                                        <a href="{{ route('payments.show', $payment) }}" class="font-mono text-xs text-brand-400 hover:text-brand-300">{{ $payment->receipt_number }}</a>
                                        <span class="font-mono text-emerald-400">{{ money($payment->amount) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @else
                <x-empty-state message="No invoice generated for this ticket yet." />
            @endif
        </div>
    </div>
</x-layouts.app>
