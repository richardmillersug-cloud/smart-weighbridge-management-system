<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\User;
use App\Models\WeightInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'receipt_number' => 'RCP-'.now()->format('Ymd').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'invoice_id' => WeightInvoice::factory(),
            'amount' => fn (array $attributes) => WeightInvoice::find($attributes['invoice_id'])->amount,
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => strtoupper(fake()->bothify('TXN-########')),
            'received_by' => User::factory(),
            'payment_date' => fake()->dateTimeBetween('-7 days'),
        ];
    }
}
