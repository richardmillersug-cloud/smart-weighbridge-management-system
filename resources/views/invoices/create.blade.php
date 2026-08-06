<x-layouts.app title="Generate Invoice">
    <x-page-header title="Generate Invoice" :subtitle="'For ticket '.$ticket->ticket_number" />

    <div class="grid max-w-4xl grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card self-start">
            <div class="card-header"><h3 class="card-title">Ticket Summary</h3></div>
            <dl class="space-y-4 px-6 py-5">
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Ticket</dt>
                    <dd class="font-mono text-sm font-bold text-brand-400">{{ $ticket->ticket_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Customer</dt>
                    <dd class="text-sm font-semibold text-slate-100">{{ $ticket->customer->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Vehicle</dt>
                    <dd class="font-mono text-sm font-semibold text-slate-100">{{ $ticket->vehicle->plate_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Vehicle Size / Capacity</dt>
                    <dd class="font-mono text-sm font-semibold text-slate-100">
                        {{ $ticket->vehicle->capacity !== null ? kg($ticket->vehicle->capacity) : 'Not recorded' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-steel-300">Product</dt>
                    <dd class="text-sm font-semibold text-slate-100">{{ $ticket->product->name }}</dd>
                </div>
                <div class="flex justify-between border-t border-steel-700/60 pt-4">
                    <dt class="text-sm font-bold text-steel-200">Net Weight</dt>
                    <dd class="font-mono text-lg font-bold text-brand-400">{{ kg($ticket->net_weight) }}</dd>
                </div>
            </dl>
        </div>

        <div class="card self-start">
            <div class="card-header"><h3 class="card-title">Billing</h3></div>
            <form method="POST" action="{{ route('invoices.store') }}" class="space-y-5 px-6 py-5">
                @csrf
                <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">

                <div>
                    <label class="label" for="amount">Amount Payable ({{ setting('currency', 'USD') }})</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" required
                           value="{{ old('amount') }}" class="input font-mono text-lg" placeholder="0.00" autofocus>
                    <p class="mt-1 text-xs text-steel-400">Enter the service charge based on the vehicle size.</p>
                    @error('amount') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-lg border border-brand-500/40 bg-brand-500/5 p-4">
                    <p class="label mb-1 text-brand-500">Invoice Amount</p>
                    <p class="font-mono text-3xl font-bold text-brand-400">{{ setting('currency', 'USD') }} <span id="amount-preview">0.00</span></p>
                </div>

                <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                    <button type="submit" class="btn-primary">Generate Invoice</button>
                    <a href="{{ route('tickets.show', $ticket) }}" class="btn-ghost">Back to Ticket</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('amount')?.addEventListener('input', (event) => {
            document.getElementById('amount-preview').textContent =
                Number(event.target.value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        });
    </script>
</x-layouts.app>
