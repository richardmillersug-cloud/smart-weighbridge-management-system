<x-layouts.app title="Edit Product">
    <x-page-header :title="'Edit — '.$product->name" />

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6 px-6 py-6">
            @csrf
            @method('PUT')
            @include('products._form')

            <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('products.show', $product) }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
