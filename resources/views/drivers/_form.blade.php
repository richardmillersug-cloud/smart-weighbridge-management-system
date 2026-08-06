@php($driver = $driver ?? null)

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="name">Full name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $driver?->name) }}" required class="input">
        @error('name') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="license_number">Licence number</label>
        <input id="license_number" name="license_number" type="text" value="{{ old('license_number', $driver?->license_number) }}" required class="input">
        @error('license_number') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="phone">Phone</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $driver?->phone) }}" class="input">
        @error('phone') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="status">Status</label>
        <select id="status" name="status" class="input">
            <option value="active" @selected(old('status', $driver?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $driver?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="input-error">{{ $message }}</p> @enderror
    </div>
</div>
