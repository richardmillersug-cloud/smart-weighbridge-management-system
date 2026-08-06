<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Product;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory()->count(8)->create();
        Vehicle::factory()->count(10)->create();
        Driver::factory()->count(10)->create();

        $products = [
            ['name' => 'Maize', 'description' => 'Dry maize grain in bulk.'],
            ['name' => 'Cement', 'description' => 'Bagged Portland cement.'],
            ['name' => 'Sand', 'description' => 'River sand for construction.'],
            ['name' => 'Gravel', 'description' => 'Crushed aggregate 10-20mm.'],
            ['name' => 'Sugar', 'description' => 'Bagged refined sugar.'],
            ['name' => 'Fertilizer', 'description' => 'Bagged NPK fertilizer.'],
            ['name' => 'Timber', 'description' => 'Sawn softwood timber.'],
            ['name' => 'Scrap Metal', 'description' => 'Mixed ferrous scrap.'],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                [...$product, 'unit' => 'kg', 'status' => 'active'],
            );
        }
    }
}
