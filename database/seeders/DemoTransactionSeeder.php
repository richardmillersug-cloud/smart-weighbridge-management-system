<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\TicketStatus;
use App\Enums\WeighingMode;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WeighbridgeStation;
use App\Models\WeighbridgeTicket;
use App\Models\WeightInvoice;
use App\Services\WeightCalculator;
use Illuminate\Database\Seeder;

class DemoTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $operator = User::where('email', 'operator@example.com')->firstOrFail();
        $station = WeighbridgeStation::defaultStation();
        $customers = Customer::all();
        $vehicles = Vehicle::all();
        $drivers = Driver::all();
        $products = Product::all();
        $calculator = app(WeightCalculator::class);

        $rate = (float) (\App\Models\Setting::get('default_rate', '5000'));

        foreach (range(6, 0) as $daysAgo) {
            $day = now()->subDays($daysAgo);
            $ticketsForDay = random_int(3, 7);

            foreach (range(1, $ticketsForDay) as $sequence) {
                $createdAt = $day->copy()->setTime(random_int(7, 16), random_int(0, 59));
                $tare = random_int(7000, 14000);
                $net = random_int(4000, 26000);
                $gross = $tare + $net;
                $deductionPct = (float) random_int(0, 5);
                $calc = $calculator->calculate($gross, $tare, $deductionPct, $rate);

                $ticket = WeighbridgeTicket::query()->forceCreate([
                    'ticket_number' => sprintf('WB-%s-%04d', $day->format('Ymd'), $sequence),
                    'station_id' => $station?->id,
                    'customer_id' => $customers->random()->id,
                    'vehicle_id' => $vehicles->random()->id,
                    'driver_id' => $drivers->random()->id,
                    'product_id' => $products->random()->id,
                    'weighing_mode' => WeighingMode::Standard,
                    'gross_weight' => $gross,
                    'gross_captured_at' => $createdAt->copy()->addMinutes(3),
                    'tare_weight' => $tare,
                    'tare_captured_at' => $createdAt->copy()->addMinutes(random_int(30, 120)),
                    'net_weight' => $calc['net_weight'],
                    'deduction_percentage' => $deductionPct,
                    'deduction_weight' => $calc['deduction_weight'],
                    'actual_weight' => $calc['actual_weight'],
                    'unit_price' => $rate,
                    'total_amount' => $calc['total_amount'],
                    'status' => TicketStatus::Completed,
                    'created_by' => $operator->id,
                    'completed_by' => $operator->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if (random_int(1, 100) <= 80) {
                    $invoice = WeightInvoice::query()->forceCreate([
                        'invoice_number' => sprintf('INV-%s-%04d', $day->format('Ymd'), $sequence),
                        'ticket_id' => $ticket->id,
                        'customer_id' => $ticket->customer_id,
                        'net_weight' => $calc['net_weight'],
                        'actual_weight' => $calc['actual_weight'],
                        'rate' => $rate,
                        'amount' => $calc['total_amount'],
                        'status' => InvoiceStatus::Pending,
                        'created_by' => $operator->id,
                        'created_at' => $createdAt->copy()->addMinutes(130),
                        'updated_at' => $createdAt->copy()->addMinutes(130),
                    ]);

                    $ticket->forceFill(['status' => TicketStatus::Invoiced])->saveQuietly();

                    if (random_int(1, 100) <= 70) {
                        Payment::query()->forceCreate([
                            'receipt_number' => sprintf('RCP-%s-%04d', $day->format('Ymd'), $sequence),
                            'invoice_id' => $invoice->id,
                            'amount' => $invoice->amount,
                            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
                            'reference' => strtoupper(fake()->bothify('TXN-########')),
                            'received_by' => $operator->id,
                            'payment_date' => $createdAt->copy()->addMinutes(140),
                            'created_at' => $createdAt->copy()->addMinutes(140),
                            'updated_at' => $createdAt->copy()->addMinutes(140),
                        ]);

                        $invoice->forceFill(['status' => InvoiceStatus::Paid])->saveQuietly();
                        $ticket->forceFill(['status' => TicketStatus::Closed])->saveQuietly();
                    }
                }
            }
        }

        $openSequence = 9000;
        foreach ([TicketStatus::AwaitingTare, TicketStatus::AwaitingGross, TicketStatus::Created] as $status) {
            $tare = random_int(7000, 14000);
            $net = random_int(4000, 26000);
            $createdAt = now()->subMinutes(random_int(10, 90));

            WeighbridgeTicket::query()->forceCreate([
                'ticket_number' => sprintf('WB-%s-%04d', now()->format('Ymd'), $openSequence++),
                'station_id' => $station?->id,
                'customer_id' => $customers->random()->id,
                'vehicle_id' => $vehicles->random()->id,
                'driver_id' => $drivers->random()->id,
                'product_id' => $products->random()->id,
                'weighing_mode' => WeighingMode::Standard,
                'tare_weight' => $status === TicketStatus::AwaitingGross ? $tare : null,
                'tare_captured_at' => $status === TicketStatus::AwaitingGross ? $createdAt->copy()->addMinutes(2) : null,
                'gross_weight' => null,
                'status' => $status,
                'unit_price' => $rate,
                'created_by' => $operator->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
