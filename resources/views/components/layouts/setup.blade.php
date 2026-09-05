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
<body class="min-h-screen px-4 py-10">
    <div class="fixed top-4 right-4">
        <x-theme-toggle />
    </div>
    <div class="mx-auto w-full max-w-3xl">
        <div class="mb-8 text-center">
            <span class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-brand-500/15 ring-1 ring-brand-500/40">
                <svg class="size-8 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v4m-9 4h18M5 11l1.5 8.5A1 1 0 0 0 7.5 20.5h9a1 1 0 0 0 1-.83L19 11M8 7h8l1 4H7l1-4Z"/>
                </svg>
            </span>
            <h1 class="font-display text-xl font-semibold tracking-widest text-white uppercase">Smart Weighbridge</h1>
            <p class="mt-1 text-[11px] font-medium tracking-[0.3em] text-brand-500 uppercase">First-run station setup</p>
        </div>

        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
