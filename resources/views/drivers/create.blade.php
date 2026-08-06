<x-layouts.app title="Register Driver">
    <x-page-header title="Register Driver" />

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('drivers.store') }}" class="space-y-6 px-6 py-6">
            @csrf
            @include('drivers._form')

            <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                <button type="submit" class="btn-primary">Register Driver</button>
                <a href="{{ route('drivers.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
