<x-layouts.app title="Cloud Sync">
    <x-page-header title="Cloud Database Sync" subtitle="Local station database → DigitalOcean mirror">
        <x-slot:actions>
            <form method="POST" action="{{ route('cloud-sync.retry') }}" class="inline">
                @csrf
                <button type="submit" class="btn-secondary" @disabled(! $status['enabled'] || ! $status['reachable'])>Retry Failed</button>
            </form>
            <form method="POST" action="{{ route('cloud-sync.full') }}" class="inline" onsubmit="return confirm('Run a full sync of all local records to the cloud?');">
                @csrf
                <button type="submit" class="btn-primary" @disabled(! $status['enabled'] || ! $status['reachable'])>Full Sync</button>
            </form>
        </x-slot:actions>
    </x-page-header>
    <x-flash />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card p-6 xl:col-span-1">
            <h3 class="card-title mb-4">Connection</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-steel-400">Sync enabled</dt>
                    <dd>
                        <span class="badge {{ $status['enabled'] ? 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/30' : 'bg-steel-500/10 text-steel-300 ring-steel-500/30' }}">
                            {{ $status['enabled'] ? 'Yes' : 'No' }}
                        </span>
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-steel-400">Configured</dt>
                    <dd>
                        <span class="badge {{ $status['configured'] ? 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/30' : 'bg-amber-500/10 text-amber-300 ring-amber-500/30' }}">
                            {{ $status['configured'] ? 'Yes' : 'No' }}
                        </span>
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-steel-400">Reachable</dt>
                    <dd>
                        <span class="badge {{ $status['reachable'] ? 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/30' : 'bg-red-500/10 text-red-300 ring-red-500/30' }}">
                            {{ $status['reachable'] ? 'Online' : 'Offline' }}
                        </span>
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-steel-400">SSL certificate</dt>
                    <dd class="text-slate-200">{{ $status['ssl_configured'] ? 'Configured' : 'Missing' }}</dd>
                </div>
                <div>
                    <dt class="text-steel-400">Cloud host</dt>
                    <dd class="mt-1 break-all font-mono text-xs text-slate-200">{{ $status['host'] ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-steel-400">Port</dt>
                    <dd class="font-mono text-slate-200">{{ $status['port'] ?: '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-steel-400">Database</dt>
                    <dd class="font-mono text-slate-200">{{ $status['database'] ?? '—' }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-xs text-steel-400">
                Local database is primary. Changes sync to DigitalOcean via queue jobs when enabled.
                Keep <code class="rounded bg-steel-800 px-1">php artisan queue:work</code> running on this PC.
            </p>
        </div>

        <div class="card p-6 xl:col-span-2">
            <h3 class="card-title mb-4">Sync summary</h3>
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-300">{{ number_format($status['counts']['success']) }}</p>
                    <p class="text-xs uppercase tracking-wider text-steel-400">Success</p>
                </div>
                <div class="rounded-lg border border-amber-500/20 bg-amber-500/5 p-4 text-center">
                    <p class="text-2xl font-bold text-amber-300">{{ number_format($status['counts']['pending']) }}</p>
                    <p class="text-xs uppercase tracking-wider text-steel-400">Pending</p>
                </div>
                <div class="rounded-lg border border-red-500/20 bg-red-500/5 p-4 text-center">
                    <p class="text-2xl font-bold text-red-300">{{ number_format($status['counts']['failed']) }}</p>
                    <p class="text-xs uppercase tracking-wider text-steel-400">Failed</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-6 overflow-hidden">
        <div class="border-b border-steel-700/60 px-6 py-4">
            <h3 class="card-title">Recent sync activity</h3>
        </div>
        <table class="table-industrial">
            <thead>
                <tr>
                    <th>Record</th>
                    <th>Action</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Last sync</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($status['recent'] as $log)
                    <tr>
                        <td class="font-mono text-sm">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</td>
                        <td>{{ $log->action }}</td>
                        <td>
                            @php
                                $statusClass = match ($log->status) {
                                    'success' => 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/30',
                                    'failed' => 'bg-red-500/10 text-red-300 ring-red-500/30',
                                    default => 'bg-amber-500/10 text-amber-300 ring-amber-500/30',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($log->status) }}</span>
                        </td>
                        <td>{{ $log->attempts }}</td>
                        <td class="text-sm text-steel-300">{{ $log->synced_at?->format('Y-m-d H:i:s') ?? $log->updated_at->format('Y-m-d H:i:s') }}</td>
                        <td class="max-w-xs truncate text-xs text-red-300" title="{{ $log->error_message }}">{{ $log->error_message ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-steel-400">No sync activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
