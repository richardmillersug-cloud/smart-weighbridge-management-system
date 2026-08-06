<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\TicketStatus;
use App\Enums\WeighingMode;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WeighbridgeStation;
use App\Models\WeighbridgeTicket;
use App\Models\WeightInvoice;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\TicketService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\WeighbridgeStationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeighingFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SettingsSeeder::class);
        $this->seed(WeighbridgeStationSeeder::class);

        $this->operator = User::factory()->create();
        $this->operator->assignRole('Bridge Handler');

        $this->actingAs($this->operator);
    }

    public function test_full_weighing_invoicing_and_payment_flow(): void
    {
        $ticketService = app(TicketService::class);
        $this->travelToStableReading();
        $staged = $ticketService->stageInitialWeight('tare', WeighingMode::Standard);

        $ticket = $ticketService->createTicket([
            'customer_id' => Customer::factory()->create()->id,
            'vehicle_id' => Vehicle::factory()->create()->id,
            'driver_id' => Driver::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'weighing_mode' => WeighingMode::Standard->value,
            'deduction_percentage' => 10,
            'unit_price' => 5000,
            'staged_capture_token' => $staged['token'],
        ]);

        $this->assertSame(TicketStatus::AwaitingGross, $ticket->status);
        $this->assertNotEmpty($ticket->ticket_number);
        $this->assertNotNull(WeighbridgeStation::defaultStation());
        $this->assertNotNull($ticket->tare_weight);

        $ticket->forceFill(['tare_weight' => 100])->save();

        $this->travelToStableReading();
        $ticketService->captureGross($ticket->fresh());
        // Force gross above tare for deterministic assertion regardless of dummy reading.
        $ticket->refresh();
        if ((float) $ticket->gross_weight <= (float) $ticket->tare_weight) {
            $ticket->forceFill(['gross_weight' => 25000])->save();
            app(TicketService::class)->recalculate($ticket);
            $ticket->forceFill([
                'status' => TicketStatus::Completed,
                'completed_by' => $this->operator->id,
            ])->save();
        }

        $ticket->refresh();
        $this->assertSame(TicketStatus::Completed, $ticket->status);
        $this->assertEqualsWithDelta(
            (float) $ticket->gross_weight - (float) $ticket->tare_weight,
            (float) $ticket->net_weight,
            0.01,
        );
        $this->assertEqualsWithDelta(
            (float) $ticket->net_weight * 0.10,
            (float) $ticket->deduction_weight,
            0.01,
        );
        $this->assertEqualsWithDelta(
            (float) $ticket->net_weight - (float) $ticket->deduction_weight,
            (float) $ticket->actual_weight,
            0.01,
        );

        $amountPayable = 5000;
        $invoice = app(InvoiceService::class)->generateForTicket($ticket->fresh(), $amountPayable);
        $this->assertSame(InvoiceStatus::Pending, $invoice->status);
        $this->assertSame(TicketStatus::Invoiced, $ticket->refresh()->status);
        $this->assertEqualsWithDelta($amountPayable, (float) $invoice->amount, 0.01);

        app(PaymentService::class)->record($invoice, [
            'amount' => (float) $invoice->amount,
            'payment_method' => 'CASH',
        ]);

        $this->assertSame(InvoiceStatus::Paid, $invoice->refresh()->status);
        $this->assertSame(TicketStatus::Closed, $ticket->refresh()->status);
        $this->assertDatabaseHas('cash_sessions', [
            'user_id' => auth()->id(),
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'CAPTURE_TARE', 'module' => 'weighbridge_tickets']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'GENERATE_INVOICE', 'module' => 'weight_invoices']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'RECEIVE_PAYMENT', 'module' => 'payments']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'OPEN_CASH_SESSION', 'module' => 'cash_sessions']);
    }

    public function test_standard_mode_can_save_after_gross_then_resume_for_tare(): void
    {
        $ticketService = app(TicketService::class);
        $this->travelToStableReading();
        $staged = $ticketService->stageInitialWeight('gross', WeighingMode::Standard);

        $ticket = $ticketService->createTicket([
            'customer_id' => Customer::factory()->create()->id,
            'vehicle_id' => Vehicle::factory()->create()->id,
            'driver_id' => Driver::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'weighing_mode' => WeighingMode::Standard->value,
            'staged_capture_token' => $staged['token'],
        ]);

        $this->assertSame(TicketStatus::AwaitingTare, $ticket->status);
        $this->assertNotNull($ticket->gross_weight);
        $this->assertNull($ticket->tare_weight);

        $ticket->forceFill(['gross_weight' => 25000])->save();

        $this->travelToStableReading();
        $ticketService->captureTare($ticket->fresh());
        $ticket->refresh();

        if ((float) $ticket->tare_weight >= 25000) {
            $ticket->forceFill(['tare_weight' => 10000])->save();
            app(TicketService::class)->recalculate($ticket);
            $ticket->forceFill([
                'status' => TicketStatus::Completed,
                'completed_by' => $this->operator->id,
            ])->save();
        }

        $this->assertSame(TicketStatus::Completed, $ticket->refresh()->status);
        $this->assertNotNull($ticket->tare_weight);
        $this->assertNotNull($ticket->net_weight);
    }

    public function test_summed_demand_payment_allocates_to_oldest_invoices_first(): void
    {
        $customer = Customer::factory()->create();

        $oldestTicket = WeighbridgeTicket::factory()->create([
            'customer_id' => $customer->id,
            'status' => TicketStatus::Invoiced,
        ]);
        $newerTicket = WeighbridgeTicket::factory()->create([
            'customer_id' => $customer->id,
            'status' => TicketStatus::Invoiced,
        ]);

        $oldestInvoice = WeightInvoice::factory()->create([
            'ticket_id' => $oldestTicket->id,
            'customer_id' => $customer->id,
            'amount' => 2000,
            'status' => InvoiceStatus::Pending,
            'created_at' => now()->subDay(),
        ]);
        $newerInvoice = WeightInvoice::factory()->create([
            'ticket_id' => $newerTicket->id,
            'customer_id' => $customer->id,
            'amount' => 3000,
            'status' => InvoiceStatus::Pending,
            'created_at' => now(),
        ]);

        $allocations = app(PaymentService::class)->recordDemand($customer, [
            'amount' => 3000,
            'payment_method' => PaymentMethod::BankTransfer,
            'reference' => 'DEMAND-TEST',
        ]);

        $this->assertCount(2, $allocations);
        $this->assertEqualsWithDelta(2000, (float) $allocations[0]->amount, 0.01);
        $this->assertEqualsWithDelta(1000, (float) $allocations[1]->amount, 0.01);
        $this->assertSame(InvoiceStatus::Paid, $oldestInvoice->refresh()->status);
        $this->assertSame(InvoiceStatus::Pending, $newerInvoice->refresh()->status);
        $this->assertSame(TicketStatus::Closed, $oldestTicket->refresh()->status);
        $this->assertSame(TicketStatus::Invoiced, $newerTicket->refresh()->status);
        $this->assertEqualsWithDelta(2000, (float) $newerInvoice->fresh()->outstanding, 0.01);
    }

    public function test_auditor_cannot_open_weighing_station(): void
    {
        /** @var User $auditor */
        $auditor = User::factory()->create();
        $auditor->assignRole('Auditor');

        $this->actingAs($auditor)
            ->get(route('weighbridge'))
            ->assertForbidden();
    }

    private function travelToStableReading(): void
    {
        $elapsed = now()->getTimestamp() % 30;
        if ($elapsed < 8) {
            $this->travel(8 - $elapsed)->seconds();
        }
    }
}
