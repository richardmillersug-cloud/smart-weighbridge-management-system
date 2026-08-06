<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Enums\WeighingMode;
use App\Models\Vehicle;
use App\Models\WeighbridgeStation;
use App\Models\WeighbridgeTicket;
use App\Services\Weighbridge\WeightReaderInterface;
use App\Support\ReferenceGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function __construct(
        private readonly WeightReaderInterface $weightReader,
        private readonly AuditService $audit,
        private readonly WeightCalculator $calculator,
    ) {}

    /**
     * Open a new weighing ticket.
     */
    public function createTicket(array $data): WeighbridgeTicket
    {
        return DB::transaction(function () use ($data): WeighbridgeTicket {
            $mode = WeighingMode::from($data['weighing_mode'] ?? WeighingMode::Standard->value);
            $station = isset($data['station_id'])
                ? WeighbridgeStation::find($data['station_id'])
                : WeighbridgeStation::defaultStation();

            $deductionEnabled = (bool) setting('deduction_enabled', '1');
            $deduction = $deductionEnabled
                ? (float) ($data['deduction_percentage'] ?? setting('default_deduction_percent', '0'))
                : 0.0;

            $unitPrice = isset($data['unit_price'])
                ? (float) $data['unit_price']
                : (float) setting('default_rate', '5000');

            $ticket = WeighbridgeTicket::create([
                'ticket_number' => ReferenceGenerator::ticketNumber(),
                'station_id' => $station?->id,
                'customer_id' => $data['customer_id'],
                'vehicle_id' => $data['vehicle_id'],
                'driver_id' => $data['driver_id'],
                'product_id' => $data['product_id'],
                'weighing_mode' => $mode,
                'supplier' => $data['supplier'] ?? null,
                'carrier' => $data['carrier'] ?? null,
                'origin' => $data['origin'] ?? null,
                'destination' => $data['destination'] ?? null,
                'goods_type' => $data['goods_type'] ?? null,
                'deduction_percentage' => $deduction,
                'unit_price' => $unitPrice,
                'remarks' => $data['remarks'] ?? null,
                'status' => $this->initialStatus($mode),
                'created_by' => Auth::id(),
            ]);

            if ($mode === WeighingMode::NetWeight) {
                $this->applyPresetTare($ticket);
            }

            if (! empty($data['staged_capture_token'])) {
                $this->applyStagedInitialWeight($ticket, (string) $data['staged_capture_token'], $mode);
            }

            $this->audit->log('CREATE_TICKET', 'weighbridge_tickets', $ticket->id, null, [
                'ticket_number' => $ticket->ticket_number,
                'weighing_mode' => $mode->value,
            ]);

            return $ticket->fresh();
        });
    }

    /**
     * Capture the first reading before a ticket is saved. The authoritative
     * reading stays server-side until createTicket consumes it.
     *
     * @return array{token: string, action: string, weight: float, captured_at: string}
     */
    public function stageInitialWeight(string $action, WeighingMode $mode): array
    {
        if (! in_array($action, ['gross', 'tare'], true)) {
            throw ValidationException::withMessages(['weight' => 'Invalid capture action.']);
        }

        if ($mode === WeighingMode::NetWeight && $action !== 'gross') {
            throw ValidationException::withMessages([
                'weight' => 'Net Weight mode accepts Gross only; Tare comes from the vehicle preset.',
            ]);
        }

        $reading = $this->readStableWeight();
        $token = (string) Str::uuid();
        $payload = [
            'user_id' => Auth::id(),
            'mode' => $mode->value,
            'action' => $action,
            'weight' => $reading->weight,
            'captured_at' => $reading->capturedAt->toIso8601String(),
        ];

        Cache::put($this->stagedCacheKey($token), $payload, now()->addMinutes(10));

        return ['token' => $token] + $payload;
    }

    public function captureGross(WeighbridgeTicket $ticket): WeighbridgeTicket
    {
        $ticket->refresh();

        return match ($ticket->weighing_mode) {
            WeighingMode::Simple => $this->captureSimple($ticket),
            WeighingMode::NetWeight => $this->captureNetWeightGross($ticket),
            default => $this->captureStandardGross($ticket),
        };
    }

    public function captureTare(WeighbridgeTicket $ticket): WeighbridgeTicket
    {
        $ticket->refresh();

        return match ($ticket->weighing_mode) {
            WeighingMode::Simple => $this->captureSimple($ticket),
            WeighingMode::NetWeight => throw ValidationException::withMessages([
                'ticket' => 'Net Weight mode uses the vehicle preset tare. Capture Gross only.',
            ]),
            default => $this->captureStandardTare($ticket),
        };
    }

    public function updateDeductions(WeighbridgeTicket $ticket, float $percentage, ?float $unitPrice = null): WeighbridgeTicket
    {
        $ticket->forceFill([
            'deduction_percentage' => max(0, $percentage),
            'unit_price' => $unitPrice ?? $ticket->unit_price,
        ])->save();

        $this->recalculate($ticket);
        $this->audit->log('WEIGHT_EDITED', 'weighbridge_tickets', $ticket->id, null, [
            'deduction_percentage' => $percentage,
            'unit_price' => $ticket->unit_price,
        ]);

        return $ticket->fresh();
    }

    public function cancel(WeighbridgeTicket $ticket, string $reason): WeighbridgeTicket
    {
        if (! $ticket->status->canBeCancelled()) {
            throw ValidationException::withMessages([
                'ticket' => "A {$ticket->status->label()} ticket cannot be cancelled.",
            ]);
        }

        $previous = $ticket->status->value;

        $ticket->forceFill([
            'status' => TicketStatus::Cancelled,
            'cancel_reason' => $reason,
        ])->save();

        $this->audit->log('CANCEL', 'weighbridge_tickets', $ticket->id, ['status' => $previous], [
            'status' => TicketStatus::Cancelled->value,
            'reason' => $reason,
        ]);

        return $ticket;
    }

    public function recalculate(WeighbridgeTicket $ticket): WeighbridgeTicket
    {
        $result = $this->calculator->calculate(
            gross: $ticket->gross_weight !== null ? (float) $ticket->gross_weight : null,
            tare: $ticket->tare_weight !== null ? (float) $ticket->tare_weight : null,
            deductionPercentage: (float) $ticket->deduction_percentage,
            unitPrice: $ticket->unit_price !== null ? (float) $ticket->unit_price : null,
        );

        $ticket->forceFill([
            'net_weight' => $ticket->gross_weight !== null && $ticket->tare_weight !== null
                ? $result['net_weight']
                : null,
            'deduction_weight' => $ticket->gross_weight !== null && $ticket->tare_weight !== null
                ? $result['deduction_weight']
                : null,
            'actual_weight' => $ticket->gross_weight !== null && $ticket->tare_weight !== null
                ? $result['actual_weight']
                : null,
            'total_amount' => $result['total_amount'],
        ])->save();

        return $ticket;
    }

    private function initialStatus(WeighingMode $mode): TicketStatus
    {
        return match ($mode) {
            WeighingMode::Standard => TicketStatus::AwaitingTare,
            WeighingMode::Simple => TicketStatus::Created,
            WeighingMode::NetWeight => TicketStatus::AwaitingGross,
        };
    }

    private function captureStandardTare(WeighbridgeTicket $ticket): WeighbridgeTicket
    {
        if ($ticket->status !== TicketStatus::AwaitingTare) {
            throw ValidationException::withMessages([
                'ticket' => "Tare weight cannot be captured while the ticket is {$ticket->status->label()}.",
            ]);
        }

        $reading = $this->readStableWeight();
        $completesTicket = $ticket->gross_weight !== null;

        if ($completesTicket && $reading->weight >= (float) $ticket->gross_weight) {
            throw ValidationException::withMessages([
                'ticket' => 'Tare weight must be less than the captured Gross weight.',
            ]);
        }

        $ticket->forceFill([
            'tare_weight' => $reading->weight,
            'tare_captured_at' => $reading->capturedAt,
            'status' => $completesTicket ? TicketStatus::Completed : TicketStatus::AwaitingGross,
            'completed_by' => $completesTicket ? Auth::id() : null,
        ])->save();

        $this->recalculate($ticket);
        $this->audit->log('CAPTURE_TARE', 'weighbridge_tickets', $ticket->id, null, $reading->toArray());
        if ($completesTicket) {
            $this->logCompletion($ticket);
        }

        return $ticket->fresh();
    }

    private function captureStandardGross(WeighbridgeTicket $ticket): WeighbridgeTicket
    {
        if ($ticket->status !== TicketStatus::AwaitingGross) {
            throw ValidationException::withMessages([
                'ticket' => 'Capture Tare first in Standard mode, then Gross.',
            ]);
        }

        $reading = $this->readStableWeight();

        if ($reading->weight <= (float) $ticket->tare_weight) {
            throw ValidationException::withMessages([
                'ticket' => sprintf(
                    'Gross weight (%s kg) must be greater than the tare weight (%s kg).',
                    number_format($reading->weight, 2),
                    number_format((float) $ticket->tare_weight, 2),
                ),
            ]);
        }

        $ticket->forceFill([
            'gross_weight' => $reading->weight,
            'gross_captured_at' => $reading->capturedAt,
            'status' => TicketStatus::Completed,
            'completed_by' => Auth::id(),
        ])->save();

        $this->recalculate($ticket);
        $this->audit->log('CAPTURE_GROSS', 'weighbridge_tickets', $ticket->id, null, $reading->toArray());
        $this->logCompletion($ticket);

        return $ticket->fresh();
    }

    private function captureSimple(WeighbridgeTicket $ticket): WeighbridgeTicket
    {
        if ($ticket->status === TicketStatus::Completed || $ticket->simple_capture_count >= 2) {
            throw ValidationException::withMessages([
                'ticket' => 'Both weights have already been captured for this Simple mode ticket.',
            ]);
        }

        if (! in_array($ticket->status, [TicketStatus::Created, TicketStatus::AwaitingGross, TicketStatus::AwaitingTare], true)) {
            throw ValidationException::withMessages([
                'ticket' => "Weights cannot be captured while the ticket is {$ticket->status->label()}.",
            ]);
        }

        $reading = $this->readStableWeight();
        $count = (int) $ticket->simple_capture_count;

        if ($count === 0) {
            $ticket->forceFill([
                'weight_one' => $reading->weight,
                'simple_capture_count' => 1,
                'status' => TicketStatus::AwaitingGross,
            ])->save();

            $this->audit->log('CAPTURE_WEIGHT', 'weighbridge_tickets', $ticket->id, null, [
                'capture' => 1,
                'weight' => $reading->weight,
            ]);

            return $ticket->fresh();
        }

        $ticket->forceFill([
            'weight_two' => $reading->weight,
            'simple_capture_count' => 2,
        ])->save();

        $assigned = $this->calculator->assignSimpleWeights(
            (float) $ticket->weight_one,
            (float) $ticket->weight_two,
        );

        if ($assigned['gross'] <= $assigned['tare']) {
            throw ValidationException::withMessages([
                'ticket' => 'The two captured weights must differ so Gross and Tare can be identified.',
            ]);
        }

        $ticket->forceFill([
            'gross_weight' => $assigned['gross'],
            'tare_weight' => $assigned['tare'],
            'gross_captured_at' => $reading->capturedAt,
            'tare_captured_at' => $reading->capturedAt,
            'status' => TicketStatus::Completed,
            'completed_by' => Auth::id(),
        ])->save();

        $this->recalculate($ticket);
        $this->audit->log('CAPTURE_WEIGHT', 'weighbridge_tickets', $ticket->id, null, [
            'capture' => 2,
            'gross' => $assigned['gross'],
            'tare' => $assigned['tare'],
        ]);
        $this->audit->log('COMPLETE_WEIGHING', 'weighbridge_tickets', $ticket->id, null, [
            'net_weight' => $ticket->net_weight,
            'actual_weight' => $ticket->actual_weight,
        ]);

        return $ticket->fresh();
    }

    private function captureNetWeightGross(WeighbridgeTicket $ticket): WeighbridgeTicket
    {
        if ($ticket->status !== TicketStatus::AwaitingGross) {
            throw ValidationException::withMessages([
                'ticket' => "Gross weight cannot be captured while the ticket is {$ticket->status->label()}.",
            ]);
        }

        if ($ticket->tare_weight === null) {
            $this->applyPresetTare($ticket);
            $ticket->refresh();
        }

        $reading = $this->readStableWeight();

        if ($reading->weight <= (float) $ticket->tare_weight) {
            throw ValidationException::withMessages([
                'ticket' => sprintf(
                    'Gross weight (%s kg) must be greater than the preset tare (%s kg).',
                    number_format($reading->weight, 2),
                    number_format((float) $ticket->tare_weight, 2),
                ),
            ]);
        }

        $ticket->forceFill([
            'gross_weight' => $reading->weight,
            'gross_captured_at' => $reading->capturedAt,
            'status' => TicketStatus::Completed,
            'completed_by' => Auth::id(),
        ])->save();

        $this->recalculate($ticket);
        $this->audit->log('CAPTURE_GROSS', 'weighbridge_tickets', $ticket->id, null, $reading->toArray());
        $this->audit->log('COMPLETE_WEIGHING', 'weighbridge_tickets', $ticket->id, null, [
            'net_weight' => $ticket->net_weight,
            'actual_weight' => $ticket->actual_weight,
        ]);

        return $ticket->fresh();
    }

    private function applyPresetTare(WeighbridgeTicket $ticket): void
    {
        $vehicle = Vehicle::findOrFail($ticket->vehicle_id);

        if ($vehicle->preset_tare === null || (float) $vehicle->preset_tare <= 0) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Net Weight mode requires the vehicle to have a stored preset tare.',
            ]);
        }

        $ticket->forceFill([
            'tare_weight' => $vehicle->preset_tare,
            'tare_captured_at' => now(),
            'status' => TicketStatus::AwaitingGross,
        ])->save();
    }

    private function applyStagedInitialWeight(
        WeighbridgeTicket $ticket,
        string $token,
        WeighingMode $mode,
    ): void {
        $key = $this->stagedCacheKey($token);
        $payload = Cache::get($key);

        if (! is_array($payload)
            || ($payload['user_id'] ?? null) !== Auth::id()
            || ($payload['mode'] ?? null) !== $mode->value
        ) {
            throw ValidationException::withMessages([
                'weight' => 'The staged weight is missing or expired. Capture it again.',
            ]);
        }

        $action = (string) $payload['action'];
        $weight = (float) $payload['weight'];
        $capturedAt = $payload['captured_at'];

        if ($mode === WeighingMode::Simple) {
            $ticket->forceFill([
                'weight_one' => $weight,
                'simple_capture_count' => 1,
                'status' => TicketStatus::AwaitingGross,
            ])->save();
            $auditAction = 'CAPTURE_WEIGHT';
        } elseif ($action === 'tare') {
            $ticket->forceFill([
                'tare_weight' => $weight,
                'tare_captured_at' => $capturedAt,
                'status' => TicketStatus::AwaitingGross,
            ])->save();
            $auditAction = 'CAPTURE_TARE';
        } else {
            if ($mode === WeighingMode::NetWeight && $weight <= (float) $ticket->tare_weight) {
                throw ValidationException::withMessages([
                    'weight' => 'Gross weight must be greater than the vehicle preset tare.',
                ]);
            }

            $completed = $mode === WeighingMode::NetWeight;
            $ticket->forceFill([
                'gross_weight' => $weight,
                'gross_captured_at' => $capturedAt,
                'status' => $completed ? TicketStatus::Completed : TicketStatus::AwaitingTare,
                'completed_by' => $completed ? Auth::id() : null,
            ])->save();
            $auditAction = 'CAPTURE_GROSS';
        }

        $this->recalculate($ticket);
        $this->audit->log($auditAction, 'weighbridge_tickets', $ticket->id, null, [
            'weight' => $weight,
            'captured_at' => $capturedAt,
            'staged_before_save' => true,
        ]);

        if ($ticket->status === TicketStatus::Completed) {
            $this->logCompletion($ticket);
        }

        Cache::forget($key);
    }

    private function stagedCacheKey(string $token): string
    {
        return 'woms:staged-weight:'.$token;
    }

    private function logCompletion(WeighbridgeTicket $ticket): void
    {
        $ticket->refresh();
        $this->audit->log('COMPLETE_WEIGHING', 'weighbridge_tickets', $ticket->id, null, [
            'net_weight' => $ticket->net_weight,
            'actual_weight' => $ticket->actual_weight,
        ]);
    }

    private function readStableWeight(): \App\Services\Weighbridge\WeightReading
    {
        if (! $this->weightReader->isConnected()) {
            throw ValidationException::withMessages([
                'weight' => 'The weighbridge indicator is not connected.',
            ]);
        }

        $reading = $this->weightReader->captureWeight();

        if (config('weighbridge.stability_required') && ! $reading->stable) {
            throw ValidationException::withMessages([
                'weight' => 'The scale reading is not stable yet. Wait for the truck to settle and try again.',
            ]);
        }

        if ($reading->weight < (float) config('weighbridge.min_weight')) {
            throw ValidationException::withMessages([
                'weight' => sprintf(
                    'Captured weight (%s kg) is below the minimum acceptable weight (%s kg).',
                    number_format($reading->weight, 2),
                    number_format((float) config('weighbridge.min_weight'), 2),
                ),
            ]);
        }

        if ($reading->weight > (float) config('weighbridge.max_weight')) {
            throw ValidationException::withMessages([
                'weight' => 'Captured weight exceeds the indicator ceiling.',
            ]);
        }

        return $reading;
    }
}
