<div
    class="flex h-full flex-col"
    wire:poll.1500ms
    x-data
    @keydown.window.prevent.f1="$wire.captureGross()"
    @keydown.window.prevent.f2="$wire.captureTare()"
    @keydown.window.prevent.f3="$wire.save()"
>
    <div class="min-h-0 flex-1 space-y-3 overflow-auto p-3 xl:p-4">
        {{-- Top: LED + Mode --}}
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-12">
            <div class="card overflow-hidden p-0 xl:col-span-5">
                <div class="led-panel">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold tracking-[0.2em] text-emerald-700 uppercase">Live Scale Weight</p>
                            <p class="led-digits mt-1">
                                {{ number_format($currentWeight, 0) }}
                                <span class="text-2xl text-emerald-700">KG</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase ring-1 {{ $connected ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/40' : 'bg-red-500/10 text-red-400 ring-red-500/40' }}">
                                <span class="size-2 rounded-full {{ $connected ? 'bg-emerald-400' : 'bg-red-500' }}"></span>
                                {{ $connected ? 'Online' : 'Offline' }}
                            </span>
                            <p class="mt-2 text-[11px] {{ $stable ? 'text-emerald-500' : 'text-amber-400' }}">
                                {{ $stable ? 'STABLE' : 'UNSTABLE' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-4 xl:col-span-7">
                <p class="card-title mb-3">Weighing Mode</p>
                <div class="flex flex-wrap gap-4">
                    @foreach ($modes as $mode)
                        <label class="mode-radio {{ $weighing_mode === $mode->value ? 'mode-radio-active' : '' }}">
                            <input type="radio" wire:model.live="weighing_mode" value="{{ $mode->value }}" class="sr-only" @disabled($activeTicketId || $stagedCaptureToken)>
                            <span>{{ $mode->label() }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-steel-400">
                    Standard: capture Gross or Tare, Save to list, click row later for the remaining weight · Simple: two captures · Net Weight: Gross only
                </p>
            </div>
        </div>

        @if ($statusMessage)
            <div class="rounded-lg border border-blue-500/30 bg-blue-500/10 px-4 py-2 text-sm text-blue-200">{{ $statusMessage }}</div>
        @endif
        @error('station')
            <div class="rounded-lg border border-red-800/60 bg-red-950/50 px-4 py-2 text-sm text-red-300">{{ $message }}</div>
        @enderror

        {{-- Mid: Form + Summary + Actions --}}
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-12">
            <div class="card p-4 xl:col-span-5">
                <div class="card-header !border-0 !px-0 !pt-0">
                    <h3 class="card-title">Truck &amp; Cargo Information</h3>
                    @if ($activeTicket)
                        <x-status-badge :status="$activeTicket->status" />
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Truck Number</label>
                        <x-lookup-input
                            wire:model.live.debounce.300ms="vehicle_input"
                            :matches="$vehicle_matches"
                            pick-method="pickVehicle"
                            :disabled="(bool) $activeTicketId"
                        />
                        @error('vehicle_input') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Ticket Number</label>
                        <input type="text" class="input bg-steel-800/80" readonly value="{{ $activeTicket?->ticket_number ?? ($stagedCaptureToken ? 'Ready on Save' : 'Capture first') }}">
                    </div>
                    <div>
                        <label class="label">Goods Name</label>
                        <x-lookup-input
                            wire:model.live.debounce.300ms="product_input"
                            :matches="$product_matches"
                            pick-method="pickProduct"
                            :disabled="(bool) $activeTicketId"
                        />
                        @error('product_input') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Supplier</label>
                        <x-lookup-input
                            wire:model.live.debounce.300ms="supplier"
                            :matches="$supplier_matches"
                            pick-method="pickSupplier"
                            :disabled="$activeTicketId && $activeTicket?->status->isFinal()"
                        />
                    </div>
                    <div>
                        <label class="label">Customer</label>
                        <x-lookup-input
                            wire:model.live.debounce.300ms="customer_input"
                            :matches="$customer_matches"
                            pick-method="pickCustomer"
                            :disabled="(bool) $activeTicketId"
                        />
                        @error('customer_input') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Driver</label>
                        <x-lookup-input
                            wire:model.live.debounce.300ms="driver_input"
                            :matches="$driver_matches"
                            pick-method="pickDriver"
                            :disabled="(bool) $activeTicketId"
                        />
                        @error('driver_input') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Destination</label>
                        <x-lookup-input
                            wire:model.live.debounce.300ms="destination"
                            :matches="$destination_matches"
                            pick-method="pickDestination"
                            :disabled="$activeTicketId && $activeTicket?->status->isFinal()"
                        />
                    </div>
                    <div class="col-span-2">
                        <label class="label">Remarks</label>
                        <input type="text" wire:model="remarks" class="input" @disabled($activeTicketId && $activeTicket?->status->isFinal())>
                    </div>
                </div>
            </div>

            <div class="card p-4 xl:col-span-4">
                <h3 class="card-title mb-3">Weight Summary</h3>
                <div class="space-y-2 text-sm">
                    @php
                        $gross = $previewGross;
                        $tare = $previewTare;
                        $net = $activeTicket?->net_weight ?? $preview['net_weight'];
                    @endphp
                    <div class="summary-row"><span>Gross Weight</span><strong>{{ $gross !== null ? number_format((float)$gross, 2) : '—' }} kg</strong></div>
                    <div class="summary-row"><span>Tare Weight</span><strong>{{ $tare !== null ? number_format((float)$tare, 2) : '—' }} kg</strong></div>
                    <div class="summary-row"><span>Net Weight</span><strong class="text-emerald-400">{{ $gross !== null && $tare !== null ? number_format((float)$net, 2) : '—' }} kg</strong></div>
                </div>
            </div>

            <div class="card flex flex-col gap-2 p-4 xl:col-span-3">
                <h3 class="card-title mb-1">Actions</h3>
                <button type="button" wire:click="captureGross" class="action-btn action-btn-primary" @disabled(! $canCaptureGross || ! $stable)>
                    Gross <span class="fkey">F1</span>
                </button>
                <button type="button" wire:click="captureTare" class="action-btn action-btn-primary" @disabled(! $canCaptureTare || ! $stable)>
                    Tare <span class="fkey">F2</span>
                </button>
                <button type="button" wire:click="save" class="action-btn action-btn-success" @disabled($activeTicketId || ! $stagedCaptureToken)>
                    Save <span class="fkey">F3</span>
                </button>
                @if ($activeTicket)
                    <a href="{{ route('tickets.print', $activeTicket) }}" target="_blank" class="action-btn action-btn-secondary text-center">
                        Print <span class="fkey">F5</span>
                    </a>
                    <a href="{{ route('tickets.print', $activeTicket) }}" target="_blank" class="action-btn action-btn-secondary text-center">Reprint</a>
                @else
                    <button type="button" class="action-btn action-btn-secondary" disabled>Print <span class="fkey">F5</span></button>
                    <button type="button" class="action-btn action-btn-secondary" disabled>Reprint</button>
                @endif
                @if ($canInvoice)
                    <a href="{{ route('invoices.create', $activeTicket) }}" class="action-btn action-btn-primary text-center">Generate Invoice</a>
                @endif
                <button type="button" wire:click="clearForm" class="action-btn action-btn-danger mt-auto">Clear</button>
            </div>
        </div>

        {{-- Filters + Grid --}}
        <div class="card p-3">
            <div class="mb-3 flex flex-wrap items-end gap-2">
                <div>
                    <label class="label">Date From</label>
                    <input type="date" wire:model.live="filterDateFrom" class="input !py-1.5">
                </div>
                <div>
                    <label class="label">Date To</label>
                    <input type="date" wire:model.live="filterDateTo" class="input !py-1.5">
                </div>
                <div>
                    <label class="label">Status</label>
                    <select wire:model.live="filterStatus" class="input !py-1.5">
                        <option value="">All</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}">{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Truck Number</label>
                    <input type="text" wire:model.live.debounce.400ms="filterTruck" class="input !py-1.5" placeholder="Plate…">
                </div>
                <div class="min-w-[180px] flex-1">
                    <label class="label">Search</label>
                    <input type="text" wire:model.live.debounce.400ms="filterSearch" class="input !py-1.5" placeholder="Ticket, customer, goods…">
                </div>
                <button type="button" wire:click="refreshGrid" class="btn-secondary !py-1.5 text-xs">Refresh</button>
                <button type="button" wire:click="resetFilters" class="btn-ghost !py-1.5 text-xs">Reset</button>
            </div>

            <div class="erp-table-wrap max-h-[280px] overflow-auto">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Ticket No</th>
                            <th>Truck No</th>
                            <th>Goods</th>
                            <th>Supplier</th>
                            <th>Customer</th>
                            <th>Gross</th>
                            <th>Tare</th>
                            <th>Net</th>
                            <th>Driver</th>
                            <th>Operator</th>
                            <th>Gross Time</th>
                            <th>Tare Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gridTickets as $row)
                            @php
                                $isOpen = in_array($row->status->value, \App\Enums\TicketStatus::openStatuses(), true);
                                $resumeHint = match ($row->status) {
                                    \App\Enums\TicketStatus::AwaitingGross => 'Resume Gross',
                                    \App\Enums\TicketStatus::AwaitingTare => 'Resume Tare',
                                    default => $isOpen ? 'Resume' : 'View',
                                };
                            @endphp
                            <tr wire:key="ticket-{{ $row->id }}" class="{{ $activeTicketId === $row->id ? 'bg-blue-500/10' : ($isOpen ? 'bg-amber-500/5' : '') }} cursor-pointer" wire:click="selectTicket({{ $row->id }})">
                                <td class="font-mono text-brand-400">{{ $row->ticket_number }}</td>
                                <td>{{ $row->vehicle?->plate_number }}</td>
                                <td>{{ $row->product?->name }}</td>
                                <td>{{ $row->supplier ?: '—' }}</td>
                                <td>{{ $row->customer?->name }}</td>
                                <td class="tabular-nums">{{ $row->gross_weight !== null ? number_format((float)$row->gross_weight, 0) : '—' }}</td>
                                <td class="tabular-nums">{{ $row->tare_weight !== null ? number_format((float)$row->tare_weight, 0) : '—' }}</td>
                                <td class="tabular-nums">{{ $row->net_weight !== null ? number_format((float)$row->net_weight, 0) : '—' }}</td>
                                <td>{{ $row->driver?->name }}</td>
                                <td>{{ $row->creator?->name }}</td>
                                <td class="whitespace-nowrap text-[11px]">{{ $row->gross_captured_at?->format('H:i') ?? '—' }}</td>
                                <td class="whitespace-nowrap text-[11px]">{{ $row->tare_captured_at?->format('H:i') ?? '—' }}</td>
                                <td><x-status-badge :status="$row->status" /></td>
                                <td>
                                    <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                                        @if ($isOpen)
                                            <button type="button" class="text-xs font-semibold text-amber-400 hover:underline" wire:click.stop="selectTicket({{ $row->id }})">
                                                {{ $resumeHint }}
                                            </button>
                                        @else
                                            <a href="{{ route('tickets.show', $row) }}" class="text-xs font-semibold text-blue-400 hover:underline">
                                                View
                                            </a>
                                        @endif
                                        <a href="{{ route('tickets.print', $row) }}" target="_blank" rel="noopener" title="Print ticket" class="inline-flex text-steel-300 hover:text-brand-400" aria-label="Print ticket">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 8.25V4.5A.75.75 0 0 1 7.5 3.75h9a.75.75 0 0 1 .75.75v3.75M6.75 15.75H5.25A2.25 2.25 0 0 1 3 13.5v-3A2.25 2.25 0 0 1 5.25 8.25h13.5A2.25 2.25 0 0 1 21 10.5v3a2.25 2.25 0 0 1-2.25 2.25h-1.5M6.75 12.75h10.5v7.5A.75.75 0 0 1 16.5 21h-9a.75.75 0 0 1-.75-.75v-7.5Z"/>
                                            </svg>
                                        </a>
                                        @if ($row->invoice?->status === \App\Enums\InvoiceStatus::Pending)
                                            @can('payments.receive')
                                                <a href="{{ route('payments.create', $row->invoice) }}" title="Receive payment" class="inline-flex text-emerald-400 hover:text-emerald-300" aria-label="Receive payment">
                                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75A2.25 2.25 0 0 1 4.5 4.5h15.75A2.25 2.25 0 0 1 22.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25V6.75Zm0 3h20.25M6 15.75h3.75"/>
                                                    </svg>
                                                </a>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="py-8 text-center text-steel-400">No weighing records for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Status bar --}}
    <footer class="status-bar shrink-0">
        <span>Operator: <strong>{{ auth()->user()->name }}</strong></span>
        <span class="sep"></span>
        <span class="{{ $dbOk ? 'text-emerald-400' : 'text-red-400' }}">DB: {{ $dbOk ? 'Connected' : 'Error' }}</span>
        <span class="sep"></span>
        <span>COM: {{ $station?->com_port ?? config('weighbridge.serial.port') }}</span>
        <span class="sep"></span>
        <span>Driver: <strong>{{ config('weighbridge.driver') === 'dummy' ? 'Simulation' : ($station?->indicator_model ?? 'XK3190') }}</strong></span>
        <span class="sep"></span>
        <span class="{{ $connected ? 'text-emerald-400' : 'text-red-400' }}">Scale: {{ $connected ? 'OK' : 'Offline' }}</span>
        <span class="sep"></span>
        <span>Records: {{ $recordCount }}</span>
        <span class="sep"></span>
        <span class="ml-auto tabular-nums" x-data="{ t: new Date().toLocaleString() }" x-init="setInterval(() => t = new Date().toLocaleString(), 1000)" x-text="t"></span>
    </footer>
</div>
