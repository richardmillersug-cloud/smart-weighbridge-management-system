<x-layouts.app title="Edit Station">
    <x-page-header title="Edit Station" subtitle="{{ $station->station_name }}" />
    <div class="card max-w-3xl p-6">
        <form method="POST" action="{{ route('stations.update', $station) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('stations._form', ['station' => $station])
            <div class="flex gap-3">
                <button class="btn-primary" type="submit">Update Station</button>
                <a href="{{ route('stations.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
