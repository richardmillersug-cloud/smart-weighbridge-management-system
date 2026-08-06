<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\User;
use App\Models\WeighbridgeTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\WeightInvoice>
 */
class WeightInvoiceFactory extends Factory
{
    public function definition(): array
    {
        $rate = fake()->randomElement([4500, 5000, 6000, 7500]);

        return [
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'ticket_id' => WeighbridgeTicket::factory(),
            'customer_id' => fn (array $attributes) => WeighbridgeTicket::find($attributes['ticket_id'])->customer_id,
            'net_weight' => fn (array $attributes) => WeighbridgeTicket::find($attributes['ticket_id'])->net_weight,
            'rate' => $rate,
            'amount' => fn (array $attributes) => round(((float) $attributes['net_weight'] / 1000) * $rate, 2),
            'status' => InvoiceStatus::Pending,
            'created_by' => User::factory(),
        ];
    }
}
