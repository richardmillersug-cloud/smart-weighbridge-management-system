<x-layouts.app title="Drivers">
    <x-page-header title="Drivers" subtitle="Registered truck drivers">
        <x-slot:actions>
            @can('drivers.create')
                <a href="{{ route('drivers.create') }}" class="btn-primary">+ Register Driver</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-full sm:w-64">
                <label class="label" for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Name, licence or phone">
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
                        <th>Licence No.</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($drivers as $driver)
                        <tr>
                            <td class="font-semibold text-slate-100">{{ $driver->name }}</td>
                            <td class="font-mono text-xs">{{ $driver->license_number }}</td>
                            <td>{{ $driver->phone ?? '—' }}</td>
                            <td><x-status-badge :status="$driver->status" /></td>
                            <td class="text-right">
                                <a href="{{ route('drivers.show', $driver) }}" class="text-xs font-semibold text-brand-400 uppercase hover:text-brand-300">View</a>
                                @can('drivers.edit')
                                    <a href="{{ route('drivers.edit', $driver) }}" class="ml-3 text-xs font-semibold text-steel-300 uppercase hover:text-slate-100">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state message="No drivers registered." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $drivers->links() }}</div>
    </div>
</x-layouts.app>
