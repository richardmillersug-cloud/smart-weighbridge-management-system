<x-layouts.app title="Customers">
    <x-page-header title="Customers" subtitle="Master list of weighbridge clients">
        <x-slot:actions>
            @can('customers.create')
                <a href="{{ route('customers.create') }}" class="btn-primary">+ New Customer</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-full sm:w-64">
                <label class="label" for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Name, code or phone">
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
                        <th>Code</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="font-mono text-xs font-semibold text-brand-400">{{ $customer->customer_code }}</td>
                            <td class="font-semibold text-slate-100">{{ $customer->name }}</td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td class="max-w-56 truncate">{{ $customer->address ?? '—' }}</td>
                            <td><x-status-badge :status="$customer->status" /></td>
                            <td class="text-right">
                                <a href="{{ route('customers.show', $customer) }}" class="text-xs font-semibold text-brand-400 uppercase hover:text-brand-300">View</a>
                                @can('customers.edit')
                                    <a href="{{ route('customers.edit', $customer) }}" class="ml-3 text-xs font-semibold text-steel-300 uppercase hover:text-slate-100">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state message="No customers found." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $customers->links() }}</div>
    </div>
</x-layouts.app>
