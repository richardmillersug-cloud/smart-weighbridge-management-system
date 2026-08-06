@if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
         class="mb-5 flex items-center justify-between gap-3 rounded-lg border border-emerald-700/50 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-300">
        <span class="flex items-center gap-2">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            {{ session('success') }}
        </span>
        <button @click="show = false" class="text-emerald-500 hover:text-emerald-300">&times;</button>
    </div>
@endif

@if (session('error'))
    <div class="mb-5 flex items-center gap-2 rounded-lg border border-red-800/50 bg-red-950/50 px-4 py-3 text-sm text-red-300">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        {{ session('error') }}
    </div>
@endif

@if ($errors->any() && ! request()->routeIs('login', 'password.*'))
    <div class="mb-5 rounded-lg border border-red-800/50 bg-red-950/50 px-4 py-3 text-sm text-red-300">
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
