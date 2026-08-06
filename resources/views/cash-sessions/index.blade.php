<x-layouts.app title="Cash Sessions">
    <x-page-header title="Daily Cash Sessions" subtitle="Sessions open automatically when cash is received — operators only close them" />
    <x-flash />

    @if ($openSession)
        <div class="card mb-6 p-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="card-title">Open Session</p>
                    <p class="mt-1 text-sm text-steel-300">Opened {{ $openSession->opened_at->format('d M Y H:i') }} · Opening cash {{ money($openSession->opening_cash) }}</p>
                    <p class="mt-1 text-sm text-emerald-400">Cash collected: {{ money($openSession->cashCollected()) }}</p>
                </div>
                <form method="POST" action="{{ route('cash-sessions.close', $openSession) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="label">Actual cash</label>
                        <input type="number" step="0.01" min="0" name="actual_cash" required class="input" value="{{ old('actual_cash', (float)$openSession->opening_cash + $openSession->cashCollected()) }}">
                    </div>
                    <div>
                        <label class="label">Closing notes</label>
                        <input type="text" name="closing_notes" class="input" value="{{ old('closing_notes') }}">
                    </div>
                    <button class="btn-danger" type="submit">Close Session</button>
                </form>
            </div>
        </div>
    @else
        <div class="card mb-6 p-5">
            <p class="text-sm text-steel-300">No open cash session. One will start automatically on the next cash payment.</p>
        </div>
    @endif

    <div class="card overflow-hidden">
        <table class="table-industrial">
            <thead>
                <tr>
                    <th>Opened</th>
                    <th>Operator</th>
                    <th>Opening</th>
                    <th>Expected</th>
                    <th>Actual</th>
                    <th>Diff</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sessions as $session)
                    <tr>
                        <td>{{ $session->opened_at->format('d M Y H:i') }}</td>
                        <td>{{ $session->user?->name }}</td>
                        <td>{{ money($session->opening_cash) }}</td>
                        <td>{{ $session->expected_cash !== null ? money($session->expected_cash) : '—' }}</td>
                        <td>{{ $session->actual_cash !== null ? money($session->actual_cash) : '—' }}</td>
                        <td>{{ $session->difference !== null ? money($session->difference) : '—' }}</td>
                        <td>{{ $session->status->label() }}</td>
                        <td><a href="{{ route('cash-sessions.show', $session) }}" class="btn-ghost text-xs">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $sessions->links() }}</div>
    </div>
</x-layouts.app>
