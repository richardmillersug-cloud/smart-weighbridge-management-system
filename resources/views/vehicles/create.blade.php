<x-layouts.app title="Register Vehicle">
    <x-page-header title="Register Vehicle" />

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('vehicles.store') }}" class="space-y-6 px-6 py-6">
            @csrf
            @include('vehicles._form')

            <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                <button type="submit" class="btn-primary">Register Vehicle</button>
                <a href="{{ route('vehicles.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
