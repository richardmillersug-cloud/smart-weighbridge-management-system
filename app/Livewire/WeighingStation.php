<?php

namespace App\Livewire;

use App\Enums\TicketStatus;
use App\Enums\WeighingMode;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Product;
use App\Models\Vehicle;
use App\Models\WeighbridgeStation;
use App\Models\WeighbridgeTicket;
use App\Services\MasterDataResolver;
use App\Services\TicketService;
use App\Services\WeightCalculator;
use App\Services\Weighbridge\WeightReaderInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.weighing')]
#[Title('Main Weighing')]
class WeighingStation extends Component
{
    use AuthorizesRequests;

    public ?int $activeTicketId = null;

    public ?string $stagedCaptureToken = null;

    public ?string $stagedCaptureAction = null;

    public ?float $stagedCaptureWeight = null;

    public ?string $stagedCapturedAt = null;

    public string $weighing_mode = 'standard';

    public ?int $station_id = null;

    public ?int $customer_id = null;

    public ?int $vehicle_id = null;

    public ?int $driver_id = null;

    public ?int $product_id = null;

    public string $customer_input = '';

    public string $vehicle_input = '';

    public string $driver_input = '';

    public string $product_input = '';

    public string $supplier = '';

    /** @var array<int, string> */
    public array $vehicle_matches = [];

    /** @var array<int, string> */
    public array $product_matches = [];

    /** @var array<int, string> */
    public array $customer_matches = [];

    /** @var array<int, string> */
    public array $driver_matches = [];

    /** @var array<int, string> */
    public array $supplier_matches = [];

    /** @var array<int, string> */
    public array $destination_matches = [];

    public bool $vehicle_exists = false;

    public bool $product_exists = false;

    public bool $customer_exists = false;

    public bool $driver_exists = false;

    public bool $supplier_exists = false;

    public bool $destination_exists = false;

    public string $carrier = '';

    public string $origin = '';

    public string $destination = '';

    public string $goods_type = '';

    public string $remarks = '';

    public string $deduction_percentage = '0';

    public string $unit_price = '';

    public string $statusMessage = '';

    #[Url]
    public string $filterDateFrom = '';

    #[Url]
    public string $filterDateTo = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterTruck = '';

    #[Url]
    public string $filterSearch = '';

    public function mount(): void
    {
        $this->filterDateFrom = $this->filterDateFrom ?: now()->toDateString();
        $this->filterDateTo = $this->filterDateTo ?: now()->toDateString();
        $this->unit_price = '0';
        $this->deduction_percentage = (string) setting('default_deduction_percent', '0');
        $this->station_id = WeighbridgeStation::defaultStation()?->id;
    }

    public function updatedVehicleInput($value): void
    {
        $this->lookupVehicle((string) $value);
    }

    public function updatedProductInput($value): void
    {
        $this->lookupProduct((string) $value);
    }

    public function updatedCustomerInput($value): void
    {
        $this->lookupCustomer((string) $value);
    }

    public function updatedDriverInput($value): void
    {
        $this->lookupDriver((string) $value);
    }

    public function updatedSupplier($value): void
    {
        $this->lookupSupplier((string) $value);
    }

    public function updatedDestination($value): void
    {
        $this->lookupDestination((string) $value);
    }

    public function pickVehicle(string $value): void
    {
        $this->vehicle_input = $value;
        $this->lookupVehicle($value);
    }

    public function pickProduct(string $value): void
    {
        $this->product_input = $value;
        $this->lookupProduct($value);
    }

    public function pickCustomer(string $value): void
    {
        $this->customer_input = $value;
        $this->lookupCustomer($value);
    }

    public function pickDriver(string $value): void
    {
        $this->driver_input = $value;
        $this->lookupDriver($value);
    }

    public function pickSupplier(string $value): void
    {
        $this->supplier = $value;
        $this->lookupSupplier($value);
    }

    public function pickDestination(string $value): void
    {
        $this->destination = $value;
        $this->lookupDestination($value);
    }

    public function updatedWeighingMode(): void
    {
        if ($this->activeTicketId === null) {
            $this->clearStagedCapture();
        }
    }

    public function updatedDeductionPercentage(): void
    {
        $this->syncActiveDeductions();
    }

    public function updatedUnitPrice(): void
    {
        $this->syncActiveDeductions();
    }

    public function save(TicketService $tickets, MasterDataResolver $resolver): void
    {
        $this->authorize('create', WeighbridgeTicket::class);

        $this->validate([
            'weighing_mode' => ['required', Rule::in(WeighingMode::values())],
            'station_id' => ['nullable', 'exists:weighbridge_stations,id'],
            'vehicle_input' => ['required', 'string', 'max:30'],
            'product_input' => ['required', 'string', 'max:255'],
            'customer_input' => ['required', 'string', 'max:255'],
            'driver_input' => ['required', 'string', 'max:255'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'carrier' => ['nullable', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'goods_type' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'deduction_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($this->stagedCaptureToken === null) {
            $this->addError('station', 'Capture the first Gross or Tare weight before saving.');

            return;
        }

        try {
            // Final DB check for every typed value on Save: reuse if found, otherwise create.
            $vehicle = $resolver->resolveVehicle($this->vehicle_input);
            $product = $resolver->resolveProduct($this->product_input);
            $customer = $resolver->resolveCustomer($this->customer_input);
            $driver = $resolver->resolveDriver($this->driver_input);

            $this->vehicle_id = $vehicle->id;
            $this->product_id = $product->id;
            $this->customer_id = $customer->id;
            $this->driver_id = $driver->id;
            $this->vehicle_input = $vehicle->plate_number;
            $this->product_input = $product->name;
            $this->customer_input = $customer->name;
            $this->driver_input = $driver->name;
            $this->vehicle_exists = true;
            $this->product_exists = true;
            $this->customer_exists = true;
            $this->driver_exists = true;

            $validated = [
                'weighing_mode' => $this->weighing_mode,
                'station_id' => $this->station_id,
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'product_id' => $product->id,
                'supplier' => $this->supplier !== '' ? trim($this->supplier) : null,
                'carrier' => $this->carrier ?: null,
                'origin' => $this->origin ?: null,
                'destination' => $this->destination !== '' ? trim($this->destination) : null,
                'goods_type' => $this->goods_type ?: null,
                'remarks' => $this->remarks ?: null,
                'deduction_percentage' => $this->deduction_percentage,
                'unit_price' => $this->unit_price,
                'staged_capture_token' => $this->stagedCaptureToken,
            ];

            $ticket = $tickets->createTicket($validated);
            $this->clearStagedCapture();

            if ($ticket->status === TicketStatus::Completed) {
                $this->activeTicketId = $ticket->id;
                $this->statusMessage = "Ticket {$ticket->ticket_number} saved and weighing completed.";

                return;
            }

            $this->parkTicketInList($ticket);
        } catch (ValidationException $e) {
            $this->addFlattenedErrors($e);
        }
    }

    public function captureGross(TicketService $tickets): void
    {
        if ($this->activeTicketId === null) {
            $this->stageInitialCapture('gross', $tickets);

            return;
        }

        $ticket = $this->requireActiveTicket();
        $this->authorize('captureWeight', $ticket);

        try {
            $tickets->captureGross($ticket);
            $ticket->refresh();
            $this->statusMessage = $ticket->status === TicketStatus::Completed
                ? sprintf('Weighing complete. Actual %s kg on %s.', number_format((float) $ticket->actual_weight, 2), $ticket->ticket_number)
                : $this->remainingWeightMessage($ticket);
        } catch (ValidationException $e) {
            $this->addFlattenedErrors($e);
        }
    }

    public function captureTare(TicketService $tickets): void
    {
        if ($this->activeTicketId === null) {
            $this->stageInitialCapture('tare', $tickets);

            return;
        }

        $ticket = $this->requireActiveTicket();
        $this->authorize('captureWeight', $ticket);

        try {
            $tickets->captureTare($ticket);
            $ticket->refresh();
            $this->statusMessage = $ticket->status === TicketStatus::Completed
                ? sprintf('Weighing complete. Actual %s kg on %s.', number_format((float) $ticket->actual_weight, 2), $ticket->ticket_number)
                : $this->remainingWeightMessage($ticket);
        } catch (ValidationException $e) {
            $this->addFlattenedErrors($e);
        }
    }

    public function selectTicket(int $ticketId): void
    {
        $ticket = WeighbridgeTicket::with(['customer', 'vehicle', 'driver', 'product'])->findOrFail($ticketId);
        $this->authorize('view', $ticket);

        $this->activeTicketId = $ticket->id;
        $this->clearStagedCapture();
        $this->weighing_mode = $ticket->weighing_mode->value;
        $this->station_id = $ticket->station_id;
        $this->customer_id = $ticket->customer_id;
        $this->vehicle_id = $ticket->vehicle_id;
        $this->driver_id = $ticket->driver_id;
        $this->product_id = $ticket->product_id;
        $this->customer_input = (string) ($ticket->customer?->name ?? '');
        $this->vehicle_input = (string) ($ticket->vehicle?->plate_number ?? '');
        $this->driver_input = (string) ($ticket->driver?->name ?? '');
        $this->product_input = (string) ($ticket->product?->name ?? '');
        $this->supplier = (string) $ticket->supplier;
        $this->carrier = (string) $ticket->carrier;
        $this->origin = (string) $ticket->origin;
        $this->destination = (string) $ticket->destination;
        $this->goods_type = (string) $ticket->goods_type;
        $this->remarks = (string) $ticket->remarks;
        $this->deduction_percentage = (string) $ticket->deduction_percentage;
        $this->unit_price = '0';
        $this->lookupVehicle($this->vehicle_input);
        $this->lookupProduct($this->product_input);
        $this->lookupCustomer($this->customer_input);
        $this->lookupDriver($this->driver_input);
        $this->lookupSupplier($this->supplier);
        $this->lookupDestination($this->destination);
        $this->statusMessage = $this->remainingWeightMessage($ticket);
        $this->resetErrorBag();
    }

    public function clearForm(): void
    {
        $this->reset(
            'activeTicketId',
            'stagedCaptureToken',
            'stagedCaptureAction',
            'stagedCaptureWeight',
            'stagedCapturedAt',
            'customer_id',
            'vehicle_id',
            'driver_id',
            'product_id',
            'customer_input',
            'vehicle_input',
            'driver_input',
            'product_input',
            'supplier',
            'carrier',
            'origin',
            'destination',
            'goods_type',
            'remarks',
            'statusMessage',
            'vehicle_matches',
            'product_matches',
            'customer_matches',
            'driver_matches',
            'supplier_matches',
            'destination_matches',
            'vehicle_exists',
            'product_exists',
            'customer_exists',
            'driver_exists',
            'supplier_exists',
            'destination_exists',
        );
        $this->weighing_mode = WeighingMode::Standard->value;
        $this->unit_price = '0';
        $this->deduction_percentage = (string) setting('default_deduction_percent', '0');
        $this->station_id = WeighbridgeStation::defaultStation()?->id;
        $this->resetErrorBag();
    }

    public function resetFilters(): void
    {
        $this->filterDateFrom = now()->toDateString();
        $this->filterDateTo = now()->toDateString();
        $this->filterStatus = '';
        $this->filterTruck = '';
        $this->filterSearch = '';
    }

    public function refreshGrid(): void
    {
        // Livewire re-render is enough; method exists for explicit Refresh button.
        $this->statusMessage = 'Grid refreshed.';
    }

    public function render(WeightReaderInterface $reader, WeightCalculator $calculator)
    {
        $station = $this->station_id
            ? WeighbridgeStation::find($this->station_id)
            : WeighbridgeStation::defaultStation();

        if (in_array(config('weighbridge.driver'), ['serial', 'xk3190'], true)) {
            $reader = \App\Services\Weighbridge\SerialWeightReaderService::fromStation($station);
        }

        $connected = $reader->isConnected();
        $activeTicket = $this->activeTicketId
            ? WeighbridgeTicket::with(['customer', 'vehicle', 'driver', 'product', 'station', 'creator'])->find($this->activeTicketId)
            : null;

        $previewGross = $activeTicket?->gross_weight !== null
            ? (float) $activeTicket->gross_weight
            : ($this->stagedCaptureAction === 'gross' ? $this->stagedCaptureWeight : null);
        $previewTare = $activeTicket?->tare_weight !== null
            ? (float) $activeTicket->tare_weight
            : ($this->stagedCaptureAction === 'tare' ? $this->stagedCaptureWeight : null);

        if ($this->activeTicketId === null
            && $this->weighing_mode === WeighingMode::NetWeight->value
            && $this->vehicle_id
        ) {
            $presetTare = Vehicle::find($this->vehicle_id)?->preset_tare;
            $previewTare = $presetTare !== null ? (float) $presetTare : null;
        }

        $preview = $calculator->calculate(
            gross: $previewGross,
            tare: $previewTare,
            deductionPercentage: (float) ($activeTicket?->deduction_percentage ?? $this->deduction_percentage ?: 0),
            unitPrice: (float) ($activeTicket?->unit_price ?? $this->unit_price ?: 0),
        );

        $ticketsQuery = WeighbridgeTicket::query()
            ->with(['customer', 'vehicle', 'driver', 'product', 'creator', 'invoice'])
            ->when($this->filterDateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterTruck, fn ($q) => $q->whereHas('vehicle', fn ($v) => $v->where('plate_number', 'like', '%'.$this->filterTruck.'%')))
            ->when($this->filterSearch, function ($q) {
                $term = '%'.$this->filterSearch.'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('ticket_number', 'like', $term)
                        ->orWhere('supplier', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term))
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $term));
                });
            })
            ->latest();

        $dbOk = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $dbOk = false;
        }

        return view('livewire.weighing-station', [
            'connected' => $connected,
            'currentWeight' => $connected ? $reader->getCurrentWeight() : 0.0,
            'stable' => $connected && $reader->isStable(),
            'activeTicket' => $activeTicket,
            'previewGross' => $previewGross,
            'previewTare' => $previewTare,
            'preview' => $preview,
            'station' => $station,
            'gridTickets' => $ticketsQuery->limit(100)->get(),
            'recordCount' => (clone $ticketsQuery)->count(),
            'dbOk' => $dbOk,
            'customers' => Customer::active()->orderBy('name')->get(['id', 'name', 'customer_code']),
            'vehicles' => Vehicle::active()->orderBy('plate_number')->get(['id', 'plate_number', 'owner_name', 'preset_tare']),
            'drivers' => Driver::active()->orderBy('name')->get(['id', 'name', 'license_number']),
            'products' => Product::active()->orderBy('name')->get(['id', 'name', 'unit']),
            'destinations' => WeighbridgeTicket::query()
                ->whereNotNull('destination')
                ->where('destination', '!=', '')
                ->distinct()
                ->orderBy('destination')
                ->limit(100)
                ->pluck('destination'),
            'suppliers' => WeighbridgeTicket::query()
                ->whereNotNull('supplier')
                ->where('supplier', '!=', '')
                ->distinct()
                ->orderBy('supplier')
                ->limit(100)
                ->pluck('supplier'),
            'stations' => WeighbridgeStation::active()->orderBy('station_name')->get(),
            'statuses' => TicketStatus::cases(),
            'modes' => WeighingMode::cases(),
            'canCaptureGross' => $activeTicket
                ? $this->canPressGross($activeTicket)
                : $this->stagedCaptureToken === null,
            'canCaptureTare' => $activeTicket
                ? $this->canPressTare($activeTicket)
                : $this->stagedCaptureToken === null
                    && $this->weighing_mode !== WeighingMode::NetWeight->value,
            'canInvoice' => $activeTicket && $activeTicket->status === TicketStatus::Completed && ! $activeTicket->invoice,
        ]);
    }

    private function canPressGross(WeighbridgeTicket $ticket): bool
    {
        return match ($ticket->weighing_mode) {
            WeighingMode::Standard => $ticket->status === TicketStatus::AwaitingGross,
            WeighingMode::Simple => in_array($ticket->status, [TicketStatus::Created, TicketStatus::AwaitingGross, TicketStatus::AwaitingTare], true)
                && $ticket->simple_capture_count < 2,
            WeighingMode::NetWeight => $ticket->status === TicketStatus::AwaitingGross,
        };
    }

    private function canPressTare(WeighbridgeTicket $ticket): bool
    {
        return match ($ticket->weighing_mode) {
            WeighingMode::Standard => in_array($ticket->status, [TicketStatus::Created, TicketStatus::AwaitingTare], true),
            WeighingMode::Simple => in_array($ticket->status, [TicketStatus::Created, TicketStatus::AwaitingGross, TicketStatus::AwaitingTare], true)
                && $ticket->simple_capture_count < 2,
            WeighingMode::NetWeight => false,
        };
    }

    private function syncActiveDeductions(): void
    {
        if (! $this->activeTicketId) {
            return;
        }

        $ticket = WeighbridgeTicket::find($this->activeTicketId);
        if (! $ticket || $ticket->status->isFinal() || $ticket->status === TicketStatus::Invoiced) {
            return;
        }

        app(TicketService::class)->updateDeductions(
            $ticket,
            (float) $this->deduction_percentage,
            $this->unit_price !== '' ? (float) $this->unit_price : null,
        );
    }

    private function stageInitialCapture(string $action, TicketService $tickets): void
    {
        $this->authorize('create', WeighbridgeTicket::class);
        $draft = new WeighbridgeTicket(['status' => TicketStatus::AwaitingTare]);
        $this->authorize('captureWeight', $draft);

        try {
            $staged = $tickets->stageInitialWeight(
                $action,
                WeighingMode::from($this->weighing_mode),
            );
            $this->stagedCaptureToken = $staged['token'];
            $this->stagedCaptureAction = $staged['action'];
            $this->stagedCaptureWeight = (float) $staged['weight'];
            $this->stagedCapturedAt = $staged['captured_at'];
            $this->statusMessage = sprintf(
                '%s weight %s kg captured. Complete the form and press Save (F3).',
                ucfirst($action),
                number_format($this->stagedCaptureWeight, 2),
            );
            $this->resetErrorBag();
        } catch (ValidationException $e) {
            $this->addFlattenedErrors($e);
        }
    }

    private function clearStagedCapture(): void
    {
        $this->stagedCaptureToken = null;
        $this->stagedCaptureAction = null;
        $this->stagedCaptureWeight = null;
        $this->stagedCapturedAt = null;
    }

    private function lookupVehicle(string $value): void
    {
        $q = trim($value);
        if ($q === '') {
            $this->vehicle_id = null;
            $this->vehicle_exists = false;
            $this->vehicle_matches = [];

            return;
        }

        $this->vehicle_matches = Vehicle::query()
            ->where('plate_number', 'like', '%'.$q.'%')
            ->orderBy('plate_number')
            ->limit(8)
            ->pluck('plate_number')
            ->all();

        $exact = Vehicle::query()
            ->whereRaw('LOWER(plate_number) = ?', [mb_strtolower($q)])
            ->first();

        $this->vehicle_id = $exact?->id;
        $this->vehicle_exists = $exact !== null;
    }

    private function lookupProduct(string $value): void
    {
        $q = trim($value);
        if ($q === '') {
            $this->product_id = null;
            $this->product_exists = false;
            $this->product_matches = [];

            return;
        }

        $this->product_matches = Product::query()
            ->where('name', 'like', '%'.$q.'%')
            ->orderBy('name')
            ->limit(8)
            ->pluck('name')
            ->all();

        $exact = Product::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($q)])
            ->first();

        $this->product_id = $exact?->id;
        $this->product_exists = $exact !== null;
    }

    private function lookupCustomer(string $value): void
    {
        $q = trim($value);
        if ($q === '') {
            $this->customer_id = null;
            $this->customer_exists = false;
            $this->customer_matches = [];

            return;
        }

        $this->customer_matches = Customer::query()
            ->where('name', 'like', '%'.$q.'%')
            ->orderBy('name')
            ->limit(8)
            ->pluck('name')
            ->all();

        $exact = Customer::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($q)])
            ->first();

        $this->customer_id = $exact?->id;
        $this->customer_exists = $exact !== null;
    }

    private function lookupDriver(string $value): void
    {
        $q = trim($value);
        if ($q === '') {
            $this->driver_id = null;
            $this->driver_exists = false;
            $this->driver_matches = [];

            return;
        }

        $this->driver_matches = Driver::query()
            ->where('name', 'like', '%'.$q.'%')
            ->orderBy('name')
            ->limit(8)
            ->pluck('name')
            ->all();

        $exact = Driver::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($q)])
            ->first();

        $this->driver_id = $exact?->id;
        $this->driver_exists = $exact !== null;
    }

    private function lookupSupplier(string $value): void
    {
        $q = trim($value);
        if ($q === '') {
            $this->supplier_exists = false;
            $this->supplier_matches = [];

            return;
        }

        $this->supplier_matches = WeighbridgeTicket::query()
            ->whereNotNull('supplier')
            ->where('supplier', 'like', '%'.$q.'%')
            ->distinct()
            ->orderBy('supplier')
            ->limit(8)
            ->pluck('supplier')
            ->all();

        $this->supplier_exists = WeighbridgeTicket::query()
            ->whereRaw('LOWER(supplier) = ?', [mb_strtolower($q)])
            ->exists();
    }

    private function lookupDestination(string $value): void
    {
        $q = trim($value);
        if ($q === '') {
            $this->destination_exists = false;
            $this->destination_matches = [];

            return;
        }

        $this->destination_matches = WeighbridgeTicket::query()
            ->whereNotNull('destination')
            ->where('destination', 'like', '%'.$q.'%')
            ->distinct()
            ->orderBy('destination')
            ->limit(8)
            ->pluck('destination')
            ->all();

        $this->destination_exists = WeighbridgeTicket::query()
            ->whereRaw('LOWER(destination) = ?', [mb_strtolower($q)])
            ->exists();
    }

    private function parkTicketInList(WeighbridgeTicket $ticket): void
    {
        $needed = $ticket->status === TicketStatus::AwaitingGross ? 'Gross' : 'Tare';

        $this->clearForm();
        $this->statusMessage = sprintf(
            'Ticket %s saved and listed below. Click it when ready to capture the remaining %s weight.',
            $ticket->ticket_number,
            $needed,
        );
    }

    private function remainingWeightMessage(WeighbridgeTicket $ticket): string
    {
        if ($ticket->status === TicketStatus::Completed) {
            return sprintf('Ticket %s is complete.', $ticket->ticket_number);
        }

        if ($ticket->status === TicketStatus::AwaitingGross) {
            return sprintf(
                'Ticket %s loaded. Tare %s kg already captured — press Gross (F1) for the remaining weight.',
                $ticket->ticket_number,
                number_format((float) $ticket->tare_weight, 2),
            );
        }

        if ($ticket->status === TicketStatus::AwaitingTare) {
            return sprintf(
                'Ticket %s loaded. Gross %s kg already captured — press Tare (F2) for the remaining weight.',
                $ticket->ticket_number,
                number_format((float) $ticket->gross_weight, 2),
            );
        }

        if ($ticket->weighing_mode === WeighingMode::Simple && (int) $ticket->simple_capture_count === 1) {
            return sprintf(
                'Ticket %s loaded. First weight %s kg captured — press Gross or Tare for the second weight.',
                $ticket->ticket_number,
                number_format((float) $ticket->weight_one, 2),
            );
        }

        return sprintf('Ticket %s loaded.', $ticket->ticket_number);
    }

    private function requireActiveTicket(): WeighbridgeTicket
    {
        abort_if($this->activeTicketId === null, 400, 'No active ticket selected. Press Save (F3) first.');

        return WeighbridgeTicket::findOrFail($this->activeTicketId);
    }

    private function addFlattenedErrors(ValidationException $e): void
    {
        foreach ($e->errors() as $messages) {
            foreach ($messages as $message) {
                $this->addError('station', $message);
            }
        }
    }
}
