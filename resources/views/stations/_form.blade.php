@php($station = $station ?? null)
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="station_name">Station name</label>
        <input id="station_name" name="station_name" class="input" required value="{{ old('station_name', $station?->station_name) }}">
        @error('station_name') <p class="input-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="label" for="indicator_model">Indicator model</label>
        <input id="indicator_model" name="indicator_model" class="input" value="{{ old('indicator_model', $station?->indicator_model ?? 'XK3190-DS17') }}">
    </div>
    <div>
        <label class="label" for="communication_type">Communication</label>
        <input id="communication_type" name="communication_type" class="input" required value="{{ old('communication_type', $station?->communication_type ?? 'RS232') }}">
    </div>
    <div>
        <label class="label" for="com_port">COM port</label>
        <input id="com_port" name="com_port" class="input font-mono uppercase" value="{{ old('com_port', $station?->com_port ?? 'COM1') }}">
    </div>
    <div>
        <label class="label" for="baud_rate">Baud rate</label>
        <input id="baud_rate" name="baud_rate" type="number" class="input" required value="{{ old('baud_rate', $station?->baud_rate ?? 9600) }}">
    </div>
    <div>
        <label class="label" for="data_bits">Data bits</label>
        <select id="data_bits" name="data_bits" class="input">
            @foreach ([8,7] as $b)
                <option value="{{ $b }}" @selected(old('data_bits', $station?->data_bits ?? 8) == $b)>{{ $b }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="parity">Parity</label>
        <select id="parity" name="parity" class="input">
            @foreach (['none','even','odd','mark','space'] as $p)
                <option value="{{ $p }}" @selected(old('parity', $station?->parity ?? 'none') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="stop_bits">Stop bits</label>
        <select id="stop_bits" name="stop_bits" class="input">
            @foreach ([1,2] as $s)
                <option value="{{ $s }}" @selected(old('stop_bits', $station?->stop_bits ?? 1) == $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="flow_control">Flow control</label>
        <select id="flow_control" name="flow_control" class="input">
            @foreach (['none','xon_xoff','rts_cts'] as $f)
                <option value="{{ $f }}" @selected(old('flow_control', $station?->flow_control ?? 'none') === $f)>{{ $f }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="status">Status</label>
        <select id="status" name="status" class="input">
            <option value="active" @selected(old('status', $station?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $station?->status) === 'inactive')>Inactive</option>
        </select>
    </div>
    <div class="flex items-center gap-2 md:col-span-2">
        <input type="checkbox" id="is_default" name="is_default" value="1" class="rounded border-steel-600" @checked(old('is_default', $station?->is_default))>
        <label for="is_default" class="text-sm text-steel-300">Default station for new tickets</label>
    </div>
</div>
