<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.disable', 'users.assign-roles',
            'settings.manage',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete',
            'drivers.view', 'drivers.create', 'drivers.edit', 'drivers.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'tickets.view', 'tickets.create', 'tickets.capture-weight', 'tickets.cancel',
            'invoices.view', 'invoices.create', 'invoices.print', 'invoices.cancel',
            'payments.view', 'payments.receive',
            'stations.view', 'stations.create', 'stations.edit', 'stations.delete',
            'cash-sessions.view', 'cash-sessions.open', 'cash-sessions.close',
            'reports.view',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('System Administrator', 'web')
            ->syncPermissions($permissions);

        Role::findOrCreate('Bridge Handler', 'web')->syncPermissions([
            'customers.view',
            'vehicles.view',
            'drivers.view',
            'products.view',
            'tickets.view', 'tickets.create', 'tickets.capture-weight', 'tickets.cancel',
            'invoices.view', 'invoices.create', 'invoices.print',
            'payments.view', 'payments.receive',
            'stations.view',
            'cash-sessions.view', 'cash-sessions.open', 'cash-sessions.close',
        ]);

        Role::findOrCreate('Auditor', 'web')->syncPermissions([
            'customers.view',
            'vehicles.view',
            'drivers.view',
            'products.view',
            'tickets.view',
            'invoices.view',
            'payments.view',
            'stations.view',
            'cash-sessions.view',
            'reports.view',
            'audit.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
