@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name') }}</title>
    <x-theme-init />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
<div class="flex min-h-screen">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-steel-700/60 bg-carbon-900/95 lg:flex">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 border-b border-steel-700/60 px-5 py-5">
            <span class="flex size-10 items-center justify-center rounded-lg bg-brand-500/15 ring-1 ring-brand-500/40">
                <svg class="size-6 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v4m-9 4h18M5 11l1.5 8.5A1 1 0 0 0 7.5 20.5h9a1 1 0 0 0 1-.83L19 11M8 7h8l1 4H7l1-4Z"/>
                </svg>
            </span>
            <span>
                <span class="block font-display text-sm font-semibold tracking-widest text-white uppercase">Smart Weighbridge</span>
                <span class="block text-[10px] font-medium tracking-[0.25em] text-brand-500 uppercase">Management System</span>
            </span>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <p class="sidebar-heading">Operations</p>

            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                Dashboard
            </a>

            @can('tickets.create')
            <a href="{{ route('weighbridge') }}" class="sidebar-link {{ request()->routeIs('weighbridge') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v5m0 0a7.5 7.5 0 0 1 7.5 7.5v3.75H4.5V15.5A7.5 7.5 0 0 1 12 8Zm0 4.5V15m8-9-2 2M4 6l2 2"/></svg>
                Weighing Console
            </a>
            @endcan

            @can('tickets.view')
            <a href="{{ route('tickets.index') }}" class="sidebar-link {{ request()->routeIs('tickets.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-12-2.25h16.5A1.5 1.5 0 0 0 22.5 14.25v-1.5a2.25 2.25 0 0 1 0-4.5v-1.5A1.5 1.5 0 0 0 21 5.25H4.5A1.5 1.5 0 0 0 3 6.75v1.5a2.25 2.25 0 0 1 0 4.5v1.5a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
                Weight Tickets
            </a>
            @endcan

            @canany(['invoices.view', 'payments.view'])
            <a href="{{ route('invoices.index') }}" class="sidebar-link {{ request()->routeIs('invoices.*', 'payments.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                Billing
            </a>
            @endcanany

            @can('invoices.view')
            <a href="{{ route('demandings.index') }}" class="sidebar-link {{ request()->routeIs('demandings.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 3v4a1 1 0 0 0 1 1h4"/></svg>
                Demandings
            </a>
            @endcan

            @can('cash-sessions.view')
            <a href="{{ route('cash-sessions.index') }}" class="sidebar-link {{ request()->routeIs('cash-sessions.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Cash Sessions
            </a>
            @endcan

            @can('stations.view')
            <a href="{{ route('stations.index') }}" class="sidebar-link {{ request()->routeIs('stations.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z"/></svg>
                Stations
            </a>
            @endcan

            @canany(['customers.view', 'vehicles.view', 'drivers.view', 'products.view'])
            <p class="sidebar-heading">Master Data</p>
            @endcanany

            @can('customers.view')
            <a href="{{ route('customers.index') }}" class="sidebar-link {{ request()->routeIs('customers.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                Customers
            </a>
            @endcan

            @can('vehicles.view')
            <a href="{{ route('vehicles.index') }}" class="sidebar-link {{ request()->routeIs('vehicles.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                Vehicles
            </a>
            @endcan

            @can('drivers.view')
            <a href="{{ route('drivers.index') }}" class="sidebar-link {{ request()->routeIs('drivers.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                Drivers
            </a>
            @endcan

            @can('products.view')
            <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                Products
            </a>
            @endcan

            @canany(['reports.view', 'audit.view'])
            <p class="sidebar-heading">Oversight</p>
            @endcanany

            @can('reports.view')
            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                Reports
            </a>
            @endcan

            @can('audit.view')
            <a href="{{ route('audit.index') }}" class="sidebar-link {{ request()->routeIs('audit.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                Audit Trail
            </a>
            @endcan

            @canany(['users.view', 'settings.manage'])
            <p class="sidebar-heading">Administration</p>
            @endcanany

            @can('users.view')
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                Users &amp; Roles
            </a>
            @endcan

            @can('settings.manage')
            <a href="{{ route('settings.edit') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'sidebar-link-active' : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                Settings
            </a>
            @endcan
        </nav>

        <div class="border-t border-steel-700/60 px-5 py-4">
            <p class="text-[10px] tracking-[0.2em] text-steel-400 uppercase">Indicator</p>
            <p class="mt-0.5 flex items-center gap-2 text-xs text-steel-300">
                <span class="size-1.5 rounded-full {{ config('weighbridge.driver') === 'dummy' ? 'bg-amber-400' : 'bg-emerald-400' }}"></span>
                {{ config('weighbridge.driver') === 'dummy' ? 'Simulation mode' : (\App\Models\WeighbridgeStation::defaultStation()?->indicator_model ?? 'XK3190') . ' / RS232' }}
            </p>
        </div>
    </aside>

    {{-- ============ MAIN ============ --}}
    <div class="flex min-w-0 flex-1 flex-col lg:pl-64">

        <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-steel-700/60 bg-carbon-900/90 px-4 backdrop-blur sm:px-6">
            <div class="min-w-0">
                <h1 class="truncate font-display text-lg font-semibold tracking-wide text-white uppercase">
                    {{ $title ?? config('app.name') }}
                </h1>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden text-right sm:block">
                    <p class="text-xs text-steel-400">{{ now()->format('D, d M Y') }}</p>
                </div>

                <x-theme-toggle />

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-3 rounded-lg border border-steel-700 bg-steel-800/60 px-3 py-1.5 text-left hover:border-steel-500">
                        <span class="flex size-8 items-center justify-center rounded-full bg-brand-500/20 font-display text-sm font-bold text-brand-400">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden md:block">
                            <span class="block text-sm font-semibold text-slate-100">{{ auth()->user()->name }}</span>
                            <span class="block text-[11px] tracking-wider text-brand-500 uppercase">{{ auth()->user()->getRoleNames()->first() }}</span>
                        </span>
                        <svg class="size-4 text-steel-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>

                    <div x-show="open" x-transition.opacity x-cloak
                         class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-steel-700 bg-steel-900 shadow-xl shadow-black/50">
                        <div class="border-b border-steel-700/60 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-100">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-steel-400">{{ auth()->user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-4 py-3 text-sm text-red-400 hover:bg-steel-800">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <x-flash />
            {{ $slot }}
        </main>

        <footer class="border-t border-steel-800 px-6 py-4 text-center text-xs text-steel-500">
            {{ config('app.name') }} &middot; {{ now()->year }}
        </footer>
    </div>
</div>

@livewireScripts
</body>
</html>
