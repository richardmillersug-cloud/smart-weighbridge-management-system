<?php

namespace Database\Seeders;

use App\Models\WeighbridgeStation;
use Illuminate\Database\Seeder;

class WeighbridgeStationSeeder extends Seeder
{
    public function run(): void
    {
        WeighbridgeStation::query()->updateOrCreate(
            ['station_name' => 'Main Weighbridge'],
            [
                'indicator_model' => 'XK3190-DS17',
                'communication_type' => 'RS232',
                'com_port' => env('WEIGHBRIDGE_COM_PORT', 'COM1'),
                'baud_rate' => 9600,
                'data_bits' => 8,
                'parity' => 'none',
                'stop_bits' => 1,
                'flow_control' => 'none',
                'status' => 'active',
                'is_default' => true,
            ],
        );
    }
}
