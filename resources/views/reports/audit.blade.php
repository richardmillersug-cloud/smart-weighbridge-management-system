<x-layouts.app title="Audit Report">
    <x-page-header title="Audit Report" :subtitle="$from->format('d M Y').' — '.$to->format('d M Y')">
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
        <div class="w-48">
            <label class="label" for="user_id">User</label>
            <select id="user_id" name="user_id" class="input">
                <option value="">All users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-48">
            <label class="label" for="module">Module</label>
            <select id="module" name="module" class="input">
                <option value="">All modules</option>
                @foreach ($modules as $module)
                    <option value="{{ $module }}" @selected(request('module') === $module)>{{ str_replace('_', ' ', ucfirst($module)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">Run Report</button>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Record</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="font-mono text-xs">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="font-semibold text-slate-100">{{ $log->user?->name ?? 'System' }}</td>
                            <td><span class="badge bg-steel-600/20 text-steel-200 ring-steel-500/40">{{ $log->action }}</span></td>
                            <td>{{ str_replace('_', ' ', $log->module) }}</td>
                            <td class="font-mono text-xs">{{ $log->record_id ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state message="No audit activity in this period." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4">{{ $logs->links() }}</div>
    </div>
</x-layouts.app>
