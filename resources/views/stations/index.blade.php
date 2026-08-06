<x-layouts.app title="Weighbridge Stations">
    <x-page-header title="Weighbridge Stations" subtitle="Indicator and serial communication configuration">
        <x-slot:actions>
            @can('stations.create')
                <a href="{{ route('stations.create') }}" class="btn-primary">Add Station</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <x-flash />

    <div class="card overflow-hidden">
        <table class="table-industrial">
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Indicator</th>
                    <th>COM</th>
                    <th>Baud</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stations as $station)
                    <tr>
                        <td>
                            <span class="font-semibold text-slate-100">{{ $station->station_name }}</span>
                            @if ($station->is_default)
                                <span class="badge ml-2 bg-blue-500/10 text-blue-300 ring-blue-500/30">Default</span>
                            @endif
                        </td>
                        <td>{{ $station->indicator_model }} · {{ $station->communication_type }}</td>
                        <td class="font-mono">{{ $station->com_port }}</td>
                        <td class="font-mono">{{ $station->baud_rate }}-{{ $station->data_bits }}-{{ strtoupper(substr($station->parity,0,1)) }}-{{ $station->stop_bits }}</td>
                        <td>{{ ucfirst($station->status) }}</td>
                        <td class="space-x-2 text-right">
                            <form method="POST" action="{{ route('stations.test', $station) }}" class="inline">@csrf<button class="btn-ghost text-xs">Test</button></form>
                            @can('stations.edit')
                                <a href="{{ route('stations.edit', $station) }}" class="btn-ghost text-xs">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-steel-400">No stations configured.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $stations->links() }}</div>
    </div>
</x-layouts.app>
