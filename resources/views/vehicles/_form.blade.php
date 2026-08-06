@php($vehicle = $vehicle ?? null)

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="plate_number">Plate number</label>
        <input id="plate_number" name="plate_number" type="text" value="{{ old('plate_number', $vehicle?->plate_number) }}" required class="input uppercase">
        @error('plate_number') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="owner_name">Owner name</label>
        <input id="owner_name" name="owner_name" type="text" value="{{ old('owner_name', $vehicle?->owner_name) }}" class="input">
        @error('owner_name') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="capacity">Rated capacity (kg)</label>
        <input id="capacity" name="capacity" type="number" step="0.01" min="0" value="{{ old('capacity', $vehicle?->capacity) }}" class="input">
        @error('capacity') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="preset_tare">Preset tare (kg)</label>
        <input id="preset_tare" name="preset_tare" type="number" step="0.01" min="0" value="{{ old('preset_tare', $vehicle?->preset_tare) }}" class="input">
        <p class="mt-1 text-[11px] text-steel-400">Required for Net Weight weighing mode.</p>
        @error('preset_tare') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label" for="status">Status</label>
        <select id="status" name="status" class="input">
            <option value="active" @selected(old('status', $vehicle?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $vehicle?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="input-error">{{ $message }}</p> @enderror
    </div>
</div>
