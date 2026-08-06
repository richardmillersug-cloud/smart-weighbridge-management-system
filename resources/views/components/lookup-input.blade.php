@props([
    'matches' => [],
    'pickMethod' => null,
    'placeholder' => '',
    'disabled' => false,
])

<div class="relative" x-data="{ open: true }" @focusin="open = true" @click.outside="open = false">
    <input
        type="text"
        {{ $attributes->wire('model') }}
        class="input"
        @if ($placeholder !== '') placeholder="{{ $placeholder }}" @endif
        @disabled($disabled)
        autocomplete="off"
    >

    @if (! $disabled && count($matches) > 0)
        <div
            x-show="open"
            x-cloak
            class="absolute z-40 mt-1 max-h-48 w-full overflow-auto rounded-lg border border-steel-700 bg-steel-900 shadow-xl"
        >
            @foreach ($matches as $match)
                <button
                    type="button"
                    class="flex w-full px-3 py-2 text-left text-sm text-slate-100 hover:bg-brand-500/15"
                    wire:click="{{ $pickMethod }}(@js($match))"
                    @click="open = false"
                >{{ $match }}</button>
            @endforeach
        </div>
    @endif
</div>
