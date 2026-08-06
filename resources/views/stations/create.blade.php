<x-layouts.app title="Add Station">
    <x-page-header title="Add Weighbridge Station" />
    <div class="card max-w-3xl p-6">
        <form method="POST" action="{{ route('stations.store') }}" class="space-y-6">
            @csrf
            @include('stations._form')
            <div class="flex gap-3">
                <button class="btn-primary" type="submit">Save Station</button>
                <a href="{{ route('stations.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
