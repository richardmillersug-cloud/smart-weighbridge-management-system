<x-layouts.app title="Demandings">
    <x-page-header title="Demandings" subtitle="Demanded invoices stored cumulatively per customer">
        <x-slot:actions>
            <a href="{{ route('invoices.index') }}" class="btn-secondary">Billing</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-4">
            <p class="label mb-1">Customers with demands</p>
            <p class="font-mono text-2xl font-bold text-slate-100">{{ $customers->total() }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-1">Demanded invoices</p>
            <p class="font-mono text-2xl font-bold text-slate-100">{{ number_format($grandInvoices) }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-1">Total demanded</p>
            <p class="font-mono text-2xl font-bold text-amber-400">{{ money($grandAmount) }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-1">Cumulative balance</p>
            <p class="font-mono text-2xl font-bold text-red-400">{{ money($grandBalance) }}</p>
        </div>
    </div>

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-full sm:w-72">
                <label class="label" for="search">Search customer</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Name, code or phone">
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
            @if (request()->filled('search'))
                <a href="{{ route('demandings.index') }}" class="btn-ghost">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Code</th>
                        <th class="text-right">Invoices</th>
                        <th class="text-right">Demanded</th>
                        <th class="text-right">Paid on demands</th>
                        <th class="text-right">Balance due</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        @php
                            $demanded = (float) $customer->demanded_amount;
                            $paid = (float) $customer->paid_on_demands;
                            $balance = max(0, $demanded - $paid);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('demandings.show', $customer) }}" class="font-semibold text-brand-400 hover:text-brand-300">
                                    {{ $customer->name }}
                                </a>
                            </td>
                            <td class="font-mono text-xs text-steel-300">{{ $customer->customer_code }}</td>
                            <td class="text-right font-mono">{{ number_format((int) $customer->demanded_count) }}</td>
                            <td class="text-right font-mono font-bold text-slate-100">{{ money($demanded) }}</td>
                            <td class="text-right font-mono text-emerald-400">{{ money($paid) }}</td>
                            <td class="text-right font-mono font-bold text-amber-400">{{ money($balance) }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('demandings.show', $customer) }}" class="btn-ghost text-xs">View</a>
                                    @can('payments.receive')
                                        <a href="{{ route('demandings.pay', $customer) }}" class="btn-primary px-3 py-1.5 text-xs">Pay Sum</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><x-empty-state message="No demanded invoices found." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $customers->links() }}</div>
    </div>
</x-layouts.app>
