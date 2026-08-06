<x-layouts.app title="New Customer">
    <x-page-header title="New Customer" subtitle="A customer code will be generated automatically" />

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('customers.store') }}" class="space-y-6 px-6 py-6">
            @csrf
            @include('customers._form')

            <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                <button type="submit" class="btn-primary">Create Customer</button>
                <a href="{{ route('customers.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
