@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-4">
        <p class="text-xs text-steel-400">
            Showing
            <span class="font-semibold text-slate-200">{{ $paginator->firstItem() ?? 0 }}</span>
            to
            <span class="font-semibold text-slate-200">{{ $paginator->lastItem() ?? 0 }}</span>
            of
            <span class="font-semibold text-slate-200">{{ $paginator->total() }}</span>
            results
        </p>

        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="rounded-lg border border-steel-700 px-3 py-1.5 text-xs text-steel-500">&larr;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-lg border border-steel-600 bg-steel-800 px-3 py-1.5 text-xs text-slate-200 hover:bg-steel-700">&larr;</a>
            @endif

            {{-- Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-xs text-steel-500">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-bold text-carbon-950">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="rounded-lg border border-steel-600 bg-steel-800 px-3 py-1.5 text-xs text-slate-200 hover:bg-steel-700">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-lg border border-steel-600 bg-steel-800 px-3 py-1.5 text-xs text-slate-200 hover:bg-steel-700">&rarr;</a>
            @else
                <span class="rounded-lg border border-steel-700 px-3 py-1.5 text-xs text-steel-500">&rarr;</span>
            @endif
        </div>
    </nav>
@endif
