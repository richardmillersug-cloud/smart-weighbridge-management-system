<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->create([
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'phone' => '+255 700 000 001',
            ])
            ->assignRole('System Administrator');

        User::factory()
            ->create([
                'name' => 'Bridge Operator',
                'email' => 'operator@example.com',
                'phone' => '+255 700 000 002',
            ])
            ->assignRole('Bridge Handler');

        User::factory()
            ->create([
                'name' => 'Independent Auditor',
                'email' => 'auditor@example.com',
                'phone' => '+255 700 000 003',
            ])
            ->assignRole('Auditor');
    }
}
