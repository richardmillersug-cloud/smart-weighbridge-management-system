@php($product = $product ?? null)

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="name">Product name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $product?->name) }}" required class="input">
        @error('name') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="unit">Unit</label>
        <select id="unit" name="unit" class="input">
            @foreach (['kg' => 'Kilogram (kg)', 't' => 'Tonne (t)'] as $value => $label)
                <option value="{{ $value }}" @selected(old('unit', $product?->unit ?? 'kg') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('unit') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="label" for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="input">{{ old('description', $product?->description) }}</textarea>
        @error('description') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="status">Status</label>
        <select id="status" name="status" class="input">
            <option value="active" @selected(old('status', $product?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $product?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="input-error">{{ $message }}</p> @enderror
    </div>
</div>
