<x-layouts.app :title="'Pay Demand — '.$customer->name">
    <x-page-header :title="'Pay Demand — '.$customer->name" subtitle="One payment allocated to the oldest invoices first" />

    <div class="grid max-w-5xl grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card self-start">
            <div class="card-header">
                <h3 class="card-title">Allocation Order</h3>
                <span class="text-xs text-steel-400">Oldest first</span>
            </div>
            <div class="max-h-[430px] overflow-auto">
                <table class="table-industrial">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>Ticket</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td class="font-mono text-xs text-steel-500">{{ $loop->iteration }}</td>
                                <td class="font-mono text-xs font-semibold text-brand-400">{{ $invoice->invoice_number }}</td>
                                <td class="font-mono text-xs text-steel-300">{{ $invoice->ticket?->ticket_number }}</td>
                                <td class="text-right font-mono font-bold text-amber-400">{{ money($invoice->outstanding) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between border-t border-steel-700/60 px-5 py-4">
                <span class="font-semibold text-slate-200">Total demand balance</span>
                <span class="font-mono text-xl font-bold text-amber-400">{{ money($totalBalance) }}</span>
            </div>
        </div>

        <div class="card self-start">
            <div class="card-header"><h3 class="card-title">Summed Payment</h3></div>
            <form method="POST" action="{{ route('demandings.pay.store', $customer) }}" class="space-y-5 px-6 py-5">
                @csrf

                <div>
                    <label class="label" for="amount">Payment amount ({{ setting('currency', 'USD') }})</label>
                    <input
                        id="amount"
                        name="amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        max="{{ $totalBalance }}"
                        value="{{ old('amount', $totalBalance) }}"
                        required
                        autofocus
                        class="input font-mono text-lg"
                    >
                    @error('amount') <p class="input-error">{{ $message }}</p> @enderror
                    <p class="mt-2 text-xs text-steel-400">
                        The system pays invoice 1 first, then invoice 2, continuing until this amount is fully allocated.
                    </p>
                </div>

                <div>
                    <label class="label" for="payment_method">Payment method</label>
                    <select id="payment_method" name="payment_method" required class="input">
                        @foreach ($methods as $method)
                            <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>
                                {{ $method->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="reference">Reference (optional)</label>
                    <input id="reference" name="reference" type="text" value="{{ old('reference') }}" class="input">
                    @error('reference') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-lg border border-brand-500/30 bg-brand-500/5 p-4 text-sm text-steel-300">
                    Example: if the demand balance is 5,000 and the customer pays 3,000,
                    the oldest invoices are fully or partially paid in order. The remaining demand will be 2,000.
                </div>

                <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                    <button type="submit" class="btn-primary">Allocate Payment</button>
                    <a href="{{ route('demandings.show', $customer) }}" class="btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
