<?php

namespace App\Http\Controllers;

use App\Models\WeighbridgeStation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', WeighbridgeStation::class);

        $stations = WeighbridgeStation::query()->latest()->paginate(20);

        return view('stations.index', compact('stations'));
    }

    public function create(): View
    {
        $this->authorize('create', WeighbridgeStation::class);

        return view('stations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WeighbridgeStation::class);

        $data = $this->validated($request);

        if (! empty($data['is_default'])) {
            WeighbridgeStation::query()->update(['is_default' => false]);
        }

        WeighbridgeStation::create($data);

        return redirect()->route('stations.index')->with('success', 'Weighbridge station created.');
    }

    public function edit(WeighbridgeStation $station): View
    {
        $this->authorize('update', $station);

        return view('stations.edit', compact('station'));
    }

    public function update(Request $request, WeighbridgeStation $station): RedirectResponse
    {
        $this->authorize('update', $station);

        $data = $this->validated($request, $station->id);

        if (! empty($data['is_default'])) {
            WeighbridgeStation::query()->where('id', '!=', $station->id)->update(['is_default' => false]);
        }

        $station->update($data);

        return redirect()->route('stations.index')->with('success', 'Station updated.');
    }

    public function destroy(WeighbridgeStation $station): RedirectResponse
    {
        $this->authorize('delete', $station);
        $station->delete();

        return redirect()->route('stations.index')->with('success', 'Station deleted.');
    }

    public function testConnection(WeighbridgeStation $station): RedirectResponse
    {
        $this->authorize('view', $station);

        $reader = \App\Services\Weighbridge\SerialWeightReaderService::fromStation($station);
        $probe = $reader->probe();

        if (! $probe['connected']) {
            return back()->with(
                'error',
                sprintf(
                    'Cannot open %s on this computer. Confirm the USB/RS232 cable is plugged into THIS PC and the COM port is correct.',
                    $probe['port'],
                ),
            );
        }

        $weightNote = $probe['weight'] !== null
            ? sprintf(' Live weight parsed: %s kg.', number_format((float) $probe['weight'], 2))
            : ' Port opened, waiting for a readable indicator frame.';

        $raw = is_string($probe['raw'] ?? null) ? $probe['raw'] : '';
        $rawNote = $raw !== ''
            ? ' Raw: '.mb_substr(preg_replace('/[^\x20-\x7E]/', '.', $raw) ?? '', 0, 48)
            : '';

        return back()->with(
            'success',
            sprintf('Connected to %s.%s%s', $probe['port'], $weightNote, $rawNote),
        );
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'station_name' => ['required', 'string', 'max:255'],
            'indicator_model' => ['nullable', 'string', 'max:255'],
            'communication_type' => ['required', 'string', 'max:30'],
            'com_port' => ['nullable', 'string', 'max:20'],
            'baud_rate' => ['required', 'integer', 'min:1200', 'max:115200'],
            'data_bits' => ['required', 'integer', 'in:7,8'],
            'parity' => ['required', 'in:none,even,odd,mark,space'],
            'stop_bits' => ['required', 'integer', 'in:1,2'],
            'flow_control' => ['required', 'in:none,xon_xoff,rts_cts'],
            'status' => ['required', 'in:active,inactive'],
            'is_default' => ['sometimes', 'boolean'],
        ]) + ['is_default' => $request->boolean('is_default')];
    }
}
