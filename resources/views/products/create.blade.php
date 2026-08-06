<x-layouts.app title="New Product">
    <x-page-header title="New Product" />

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('products.store') }}" class="space-y-6 px-6 py-6">
            @csrf
            @include('products._form')

            <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                <button type="submit" class="btn-primary">Create Product</button>
                <a href="{{ route('products.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
