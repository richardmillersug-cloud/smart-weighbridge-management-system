<x-layouts.app title="Edit Driver">
    <x-page-header :title="'Edit — '.$driver->name" />

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('drivers.update', $driver) }}" class="space-y-6 px-6 py-6">
            @csrf
            @method('PUT')
            @include('drivers._form')

            <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('drivers.show', $driver) }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
