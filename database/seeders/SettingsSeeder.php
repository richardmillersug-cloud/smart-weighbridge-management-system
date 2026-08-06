<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Company
            'company_name' => 'Smart Weighbridge Ltd',
            'company_address' => 'Plot 12, Industrial Area',
            'company_phone' => '+255 700 000 000',
            'company_email' => 'info@smartweighbridge.example',
            'company_logo' => null,
            'currency' => 'USD',

            // Ticket / document numbering
            'ticket_prefix' => 'WB',
            'invoice_prefix' => 'INV',
            'receipt_prefix' => 'RCP',

            // Weighing
            'default_rate' => '5000',
            'weight_unit' => 'kg',
            'deduction_enabled' => '1',
            'default_deduction_percent' => '0',
            'allow_manual_weight' => '0',
            'stable_weight_timeout' => '5',

            // Printing
            'ticket_template' => 'default',
            'invoice_template' => 'default',
            'receipt_template' => 'default',
            'default_printer' => null,

            // Security
            'session_timeout_minutes' => '120',
            'password_min_length' => '8',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
