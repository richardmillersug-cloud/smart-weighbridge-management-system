<x-layouts.app title="Operator Report">
    <x-page-header title="Operator Report" :subtitle="$from->format('d M Y').' — '.$to->format('d M Y')">
        <x-slot:actions>
            <button onclick="window.print()" class="btn-secondary no-print">Print</button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="no-print mb-6 flex flex-wrap items-end gap-3">
        <div class="w-44">
            <label class="label" for="from">From</label>
            <input id="from" type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input">
        </div>
        <div class="w-44">
            <label class="label" for="to">To</label>
            <input id="to" type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input">
        </div>
        <button type="submit" class="btn-primary">Run Report</button>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Operator</th>
                        <th class="text-right">Tickets Created</th>
                        <th class="text-right">Weighings Completed</th>
                        <th class="text-right">Net Weight (t)</th>
                        <th class="text-right">Invoices Issued</th>
                        <th class="text-right">Payments Taken</th>
                        <th class="text-right">Amount Collected</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($operators as $operator)
                        <tr>
                            <td class="font-semibold text-slate-100">{{ $operator->name }}</td>
                            <td class="text-right font-mono">{{ $operator->tickets_count }}</td>
                            <td class="text-right font-mono">{{ $operator->completed_count }}</td>
                            <td class="text-right font-mono">{{ number_format(((float) $operator->net_weight_sum) / 1000, 2) }}</td>
                            <td class="text-right font-mono">{{ $operator->invoices_count }}</td>
                            <td class="text-right font-mono">{{ $operator->payments_count }}</td>
                            <td class="text-right font-mono font-bold text-emerald-400">{{ money($operator->payments_sum ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state message="No operator activity in this period." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
