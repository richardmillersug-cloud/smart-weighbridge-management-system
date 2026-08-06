<x-layouts.app title="Receive Payment">
    <x-page-header title="Receive Payment" :subtitle="'Against invoice '.$invoice->invoice_number" />

    <div class="grid max-w-4xl grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card self-start">
            <div class="card-header"><h3 class="card-title">Invoice Summary</h3></div>
            <dl class="space-y-4 px-6 py-5">
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Invoice</dt>
                    <dd class="font-mono text-sm font-bold text-brand-400">{{ $invoice->invoice_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Customer</dt>
                    <dd class="text-sm font-semibold text-slate-100">{{ $invoice->customer->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Ticket</dt>
                    <dd class="font-mono text-sm text-slate-100">{{ $invoice->ticket->ticket_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Invoice Amount</dt>
                    <dd class="font-mono text-sm font-semibold text-slate-100">{{ money($invoice->amount) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Already Paid</dt>
                    <dd class="font-mono text-sm text-emerald-400">{{ money($invoice->amount_paid) }}</dd>
                </div>
                <div class="flex justify-between border-t border-steel-700/60 pt-4">
                    <dt class="text-sm font-bold text-steel-200">Balance Due</dt>
                    <dd class="font-mono text-lg font-bold text-amber-400">{{ money($invoice->outstanding) }}</dd>
                </div>
            </dl>
        </div>

        <div class="card self-start">
            <div class="card-header"><h3 class="card-title">Payment Details</h3></div>
            <form method="POST" action="{{ route('payments.store') }}" class="space-y-5 px-6 py-5">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                <div>
                    <label class="label" for="amount">Amount ({{ setting('currency', 'USD') }})</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01"
                           max="{{ $invoice->outstanding }}" value="{{ old('amount', $invoice->outstanding) }}"
                           required class="input font-mono text-lg">
                    @error('amount') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="payment_method">Payment method</label>
                    <select id="payment_method" name="payment_method" required class="input">
                        @foreach ($methods as $method)
                            <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>{{ $method->label() }}</option>
                        @endforeach
                    </select>
                    @error('payment_method') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="reference">Reference (optional)</label>
                    <input id="reference" name="reference" type="text" value="{{ old('reference') }}" class="input" placeholder="Transaction / cheque number">
                    @error('reference') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                    <button type="submit" class="btn-primary">Record Payment &amp; Print Receipt</button>
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
