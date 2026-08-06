<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Enums\WeighingMode;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\WeighbridgeTicket>
 */
class WeighbridgeTicketFactory extends Factory
{
    public function definition(): array
    {
        $tare = fake()->numberBetween(7000, 14000);
        $net = fake()->numberBetween(5000, 28000);
        $gross = $tare + $net;
        $capturedAt = fake()->dateTimeBetween('-7 days');
        $deduction = 0;
        $actual = $net;

        return [
            'ticket_number' => 'WB-'.now()->format('Ymd').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_id' => Customer::factory(),
            'vehicle_id' => Vehicle::factory(),
            'driver_id' => Driver::factory(),
            'product_id' => Product::factory(),
            'weighing_mode' => WeighingMode::Standard,
            'gross_weight' => $gross,
            'gross_captured_at' => $capturedAt,
            'tare_weight' => $tare,
            'tare_captured_at' => fake()->dateTimeBetween($capturedAt),
            'net_weight' => $net,
            'deduction_percentage' => $deduction,
            'deduction_weight' => 0,
            'actual_weight' => $actual,
            'unit_price' => 5000,
            'total_amount' => round(($actual / 1000) * 5000, 2),
            'status' => TicketStatus::Completed,
            'created_by' => User::factory(),
            'completed_by' => fn (array $attributes) => $attributes['created_by'],
        ];
    }

    public function created(): static
    {
        return $this->state(fn (): array => [
            'gross_weight' => null,
            'gross_captured_at' => null,
            'tare_weight' => null,
            'tare_captured_at' => null,
            'net_weight' => null,
            'deduction_weight' => null,
            'actual_weight' => null,
            'total_amount' => null,
            'status' => TicketStatus::AwaitingTare,
            'completed_by' => null,
        ]);
    }

    public function awaitingGross(): static
    {
        return $this->state(fn (): array => [
            'gross_weight' => null,
            'gross_captured_at' => null,
            'net_weight' => null,
            'deduction_weight' => null,
            'actual_weight' => null,
            'total_amount' => null,
            'status' => TicketStatus::AwaitingGross,
            'completed_by' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => TicketStatus::Cancelled,
            'cancel_reason' => fake()->sentence(),
        ]);
    }
}
