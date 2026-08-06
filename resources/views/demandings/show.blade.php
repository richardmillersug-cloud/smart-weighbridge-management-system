<x-layouts.app :title="'Demands — '.$customer->name">
    <x-page-header :title="$customer->name" subtitle="Cumulative demanded invoices for this customer">
        <x-slot:actions>
            <a href="{{ route('demandings.index') }}" class="btn-secondary">All Demandings</a>
            @if ($totalBalance > 0)
                @can('payments.receive')
                    <a href="{{ route('demandings.pay', $customer) }}" class="btn-primary">Pay Total Demand</a>
                @endcan
            @endif
            <a href="{{ route('customers.show', $customer) }}" class="btn-ghost">Customer profile</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card p-4">
            <p class="label mb-1">Demanded invoices</p>
            <p class="font-mono text-2xl font-bold text-slate-100">{{ $invoices->count() }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-1">Total demanded</p>
            <p class="font-mono text-2xl font-bold text-amber-400">{{ money($totalAmount) }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-1">Cumulative balance</p>
            <p class="font-mono text-2xl font-bold text-red-400">{{ money($totalBalance) }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Demanded invoices</h3>
            <p class="text-xs text-steel-400">Partial payments shown against each invoice</p>
        </div>

        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Ticket / Truck</th>
                        <th>Goods</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Balance</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-xs font-semibold text-brand-400 hover:text-brand-300">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <a href="{{ route('tickets.show', $invoice->ticket) }}" class="font-mono text-xs text-steel-300 hover:text-slate-100">
                                        {{ $invoice->ticket?->ticket_number }}
                                    </a>
                                    <span class="font-mono text-[11px] text-steel-500">{{ $invoice->ticket?->vehicle?->plate_number }}</span>
                                </div>
                            </td>
                            <td>{{ $invoice->ticket?->product?->name ?? '—' }}</td>
                            <td class="text-right font-mono font-bold text-slate-100">{{ money($invoice->amount) }}</td>
                            <td class="text-right font-mono text-emerald-400">{{ money($invoice->amount_paid) }}</td>
                            <td class="text-right font-mono font-bold text-amber-400">{{ money($invoice->outstanding) }}</td>
                            <td class="text-xs text-steel-400">{{ $invoice->created_at->format('d M Y H:i') }}</td>
                            <td class="text-right">
                                @can('payments.receive')
                                    <a href="{{ route('payments.create', $invoice) }}" class="btn-primary px-3 py-1.5 text-xs">Pay</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"><x-empty-state message="No open demanded invoices for this customer." /></td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($invoices->isNotEmpty())
                    <tfoot>
                        <tr class="border-t border-steel-700/60">
                            <td colspan="3" class="px-4 py-3 text-sm font-semibold text-slate-200">Cumulative total</td>
                            <td class="px-4 py-3 text-right font-mono font-bold">{{ money($totalAmount) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-emerald-400">{{ money($totalPaid) }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-amber-400">{{ money($totalBalance) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-layouts.app>
