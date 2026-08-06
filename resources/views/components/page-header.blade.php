@props(['title', 'subtitle' => null])

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h2 class="font-display text-xl font-semibold tracking-wide text-white uppercase">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-1 text-sm text-steel-400">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
