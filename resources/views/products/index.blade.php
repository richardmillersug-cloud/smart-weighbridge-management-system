<x-layouts.app title="Products">
    <x-page-header title="Products" subtitle="Materials weighed on the bridge">
        <x-slot:actions>
            @can('products.create')
                <a href="{{ route('products.create') }}" class="btn-primary">+ New Product</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-full sm:w-64">
                <label class="label" for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Product name">
            </div>
            <div class="w-40">
                <label class="label" for="status">Status</label>
                <select id="status" name="status" class="input">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="font-semibold text-slate-100">{{ $product->name }}</td>
                            <td class="max-w-64 truncate">{{ $product->description ?? '—' }}</td>
                            <td class="uppercase">{{ $product->unit }}</td>
                            <td><x-status-badge :status="$product->status" /></td>
                            <td class="text-right">
                                <a href="{{ route('products.show', $product) }}" class="text-xs font-semibold text-brand-400 uppercase hover:text-brand-300">View</a>
                                @can('products.edit')
                                    <a href="{{ route('products.edit', $product) }}" class="ml-3 text-xs font-semibold text-steel-300 uppercase hover:text-slate-100">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state message="No products defined." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $products->links() }}</div>
    </div>
</x-layouts.app>
