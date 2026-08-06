<x-layouts.app :title="'Audit Entry #'.$auditLog->id">
    <x-page-header :title="'Audit Entry #'.$auditLog->id" :subtitle="$auditLog->created_at->format('d M Y H:i:s')">
        <x-slot:actions>
            <a href="{{ route('audit.index') }}" class="btn-ghost">&larr; Back to trail</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-5">
        <div class="card p-4">
            <p class="label mb-0.5">User</p>
            <p class="text-sm font-semibold text-slate-100">{{ $auditLog->user?->name ?? 'System' }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Action</p>
            <span class="badge bg-steel-600/20 text-steel-200 ring-steel-500/40">{{ $auditLog->action }}</span>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Module</p>
            <p class="text-sm font-semibold text-slate-100">{{ str_replace('_', ' ', $auditLog->module) }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">Record ID</p>
            <p class="font-mono text-sm font-semibold text-slate-100">{{ $auditLog->record_id ?? '—' }}</p>
        </div>
        <div class="card p-4">
            <p class="label mb-0.5">IP Address</p>
            <p class="font-mono text-sm font-semibold text-slate-100">{{ $auditLog->ip_address ?? '—' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Old Value</h3></div>
            <pre class="overflow-x-auto px-6 py-5 font-mono text-xs leading-relaxed text-red-300">{{ $auditLog->old_value ? json_encode($auditLog->old_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">New Value</h3></div>
            <pre class="overflow-x-auto px-6 py-5 font-mono text-xs leading-relaxed text-emerald-300">{{ $auditLog->new_value ? json_encode($auditLog->new_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
        </div>
    </div>
</x-layouts.app>
