<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Vehicle::class, 'vehicle');
    }

    public function index(Request $request): View
    {
        $vehicles = Vehicle::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(fn ($q) => $q
                    ->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('plate_number')
            ->paginate(15)
            ->withQueryString();

        return view('vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create($request->validated());

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', "Vehicle {$vehicle->plate_number} registered.");
    }

    public function show(Vehicle $vehicle): View
    {
        $tickets = $vehicle->tickets()
            ->with(['customer', 'product'])
            ->latest()
            ->paginate(10);

        return view('vehicles.show', compact('vehicle', 'tickets'));
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', "Vehicle {$vehicle->plate_number} updated.");
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()
            ->route('vehicles.index')
            ->with('success', "Vehicle {$vehicle->plate_number} archived.");
    }
}
