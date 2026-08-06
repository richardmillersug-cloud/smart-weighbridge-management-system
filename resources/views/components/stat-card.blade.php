@props([
    'label',
    'value',
    'sub' => null,
    'accent' => 'brand', // brand | emerald | sky | red
])

@php
    $accentClasses = [
        'brand' => 'text-brand-400 bg-brand-500/10 ring-brand-500/30',
        'emerald' => 'text-emerald-400 bg-emerald-500/10 ring-emerald-500/30',
        'sky' => 'text-sky-400 bg-sky-500/10 ring-sky-500/30',
        'red' => 'text-red-400 bg-red-500/10 ring-red-500/30',
    ][$accent] ?? 'text-brand-400 bg-brand-500/10 ring-brand-500/30';

    $cardAccent = in_array($accent, ['brand', 'emerald', 'sky', 'red'], true)
        ? $accent
        : 'brand';
@endphp

<div {{ $attributes->merge(['class' => "card stat-card stat-card-{$cardAccent} p-5"]) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="font-display text-[11px] font-semibold tracking-widest text-steel-300 uppercase">{{ $label }}</p>
            <p class="stat-value mt-2">{{ $value }}</p>
            @if ($sub)
                <p class="mt-1 text-xs text-steel-400">{{ $sub }}</p>
            @endif
        </div>
        @isset($icon)
            <span class="flex size-11 shrink-0 items-center justify-center rounded-lg ring-1 {{ $accentClasses }}">
                {{ $icon }}
            </span>
        @endisset
    </div>
</div>
