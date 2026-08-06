<x-layouts.app title="Vehicles">
    <x-page-header title="Vehicles" subtitle="Registered trucks and trailers">
        <x-slot:actions>
            @can('vehicles.create')
                <a href="{{ route('vehicles.create') }}" class="btn-primary">+ Register Vehicle</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-full sm:w-64">
                <label class="label" for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Plate number or owner">
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
                        <th>Plate Number</th>
                        <th>Owner</th>
                        <th class="text-right">Capacity (kg)</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicles as $vehicle)
                        <tr>
                            <td class="font-mono font-bold text-slate-100">{{ $vehicle->plate_number }}</td>
                            <td>{{ $vehicle->owner_name ?? '—' }}</td>
                            <td class="text-right font-mono">{{ $vehicle->capacity !== null ? number_format((float) $vehicle->capacity, 0) : '—' }}</td>
                            <td><x-status-badge :status="$vehicle->status" /></td>
                            <td class="text-right">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="text-xs font-semibold text-brand-400 uppercase hover:text-brand-300">View</a>
                                @can('vehicles.edit')
                                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="ml-3 text-xs font-semibold text-steel-300 uppercase hover:text-slate-100">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state message="No vehicles registered." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $vehicles->links() }}</div>
    </div>
</x-layouts.app>
