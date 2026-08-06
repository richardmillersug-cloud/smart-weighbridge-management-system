<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Product;
use App\Models\Vehicle;

class MasterDataResolver
{
    public function resolveVehicle(string $plateNumber): Vehicle
    {
        $plate = trim($plateNumber);

        $existing = Vehicle::withTrashed()
            ->whereRaw('LOWER(plate_number) = ?', [mb_strtolower($plate)])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            if ($existing->status !== Vehicle::STATUS_ACTIVE) {
                $existing->forceFill(['status' => Vehicle::STATUS_ACTIVE])->save();
            }

            return $existing;
        }

        return Vehicle::create([
            'plate_number' => mb_strtoupper($plate),
            'status' => Vehicle::STATUS_ACTIVE,
        ]);
    }

    public function resolveCustomer(string $name): Customer
    {
        $name = trim($name);

        $existing = Customer::withTrashed()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            if ($existing->status !== Customer::STATUS_ACTIVE) {
                $existing->forceFill(['status' => Customer::STATUS_ACTIVE])->save();
            }

            return $existing;
        }

        return Customer::create([
            'customer_code' => Customer::nextCode(),
            'name' => $name,
            'status' => Customer::STATUS_ACTIVE,
        ]);
    }

    public function resolveDriver(string $name): Driver
    {
        $name = trim($name);

        $existing = Driver::withTrashed()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            if ($existing->status !== Driver::STATUS_ACTIVE) {
                $existing->forceFill(['status' => Driver::STATUS_ACTIVE])->save();
            }

            return $existing;
        }

        return Driver::create([
            'name' => $name,
            'status' => Driver::STATUS_ACTIVE,
        ]);
    }

    public function resolveProduct(string $name): Product
    {
        $name = trim($name);

        $existing = Product::withTrashed()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            if ($existing->status !== Product::STATUS_ACTIVE) {
                $existing->forceFill(['status' => Product::STATUS_ACTIVE])->save();
            }

            return $existing;
        }

        return Product::create([
            'name' => $name,
            'unit' => setting('weight_unit', 'kg') ?: 'kg',
            'status' => Product::STATUS_ACTIVE,
        ]);
    }
}
