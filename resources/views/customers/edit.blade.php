<x-layouts.app title="Edit Customer">
    <x-page-header :title="'Edit — '.$customer->name" :subtitle="$customer->customer_code" />

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-6 px-6 py-6">
            @csrf
            @method('PUT')
            @include('customers._form')

            <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('customers.show', $customer) }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
