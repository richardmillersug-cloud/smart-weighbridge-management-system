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
<body class="h-full overflow-hidden bg-carbon-950 text-slate-200 antialiased">
    <div class="flex h-screen flex-col">
        <header class="flex shrink-0 items-center justify-between border-b border-steel-700/60 bg-carbon-900/95 px-4 py-2">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="font-display text-sm font-semibold tracking-widest text-white uppercase hover:text-brand-400">
                    WOMS
                </a>
                <span class="text-steel-600">|</span>
                <span class="font-display text-xs tracking-widest text-steel-300 uppercase">Main Weighing</span>
            </div>
            <div class="flex items-center gap-3 text-xs text-steel-300">
                <a href="{{ route('dashboard') }}" class="btn-ghost text-xs">Dashboard</a>
                <a href="{{ route('tickets.index') }}" class="btn-ghost text-xs">Tickets</a>
                <x-theme-toggle />
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost text-xs text-red-300">Logout</button>
                </form>
            </div>
        </header>

        <main class="min-h-0 flex-1 overflow-hidden">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
