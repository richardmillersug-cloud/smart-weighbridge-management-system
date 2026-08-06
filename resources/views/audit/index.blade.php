<x-layouts.app title="Audit Trail">
    <x-page-header title="Audit Trail" subtitle="Full system activity log" />

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-48">
                <label class="label" for="user_id">User</label>
                <select id="user_id" name="user_id" class="input">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-44">
                <label class="label" for="module">Module</label>
                <select id="module" name="module" class="input">
                    <option value="">All modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ str_replace('_', ' ', ucfirst($module)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-44">
                <label class="label" for="action">Action</label>
                <select id="action" name="action" class="input">
                    <option value="">All actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="label" for="from">From</label>
                <input id="from" type="date" name="from" value="{{ request('from') }}" class="input">
            </div>
            <div class="w-40">
                <label class="label" for="to">To</label>
                <input id="to" type="date" name="to" value="{{ request('to') }}" class="input">
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>

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
                        <th class="text-right">Details</th>
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
                            <td class="text-right">
                                <a href="{{ route('audit.show', $log) }}" class="text-xs font-semibold text-brand-400 uppercase hover:text-brand-300">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state message="No audit entries match your filters." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $logs->links() }}</div>
    </div>
</x-layouts.app>
