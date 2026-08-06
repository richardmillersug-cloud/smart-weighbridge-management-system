@php($customer = $customer ?? null)

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="name">Customer name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $customer?->name) }}" required class="input">
        @error('name') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="phone">Phone</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $customer?->phone) }}" class="input">
        @error('phone') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="address">Address</label>
        <input id="address" name="address" type="text" value="{{ old('address', $customer?->address) }}" class="input">
        @error('address') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="status">Status</label>
        <select id="status" name="status" class="input">
            <option value="active" @selected(old('status', $customer?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $customer?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="input-error">{{ $message }}</p> @enderror
    </div>
</div>
