<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::findOrCreate('cloud-sync.manage', 'web');

        $admin = Role::findByName('System Administrator', 'web');
        $admin->givePermissionTo('cloud-sync.manage');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::findByName('System Administrator', 'web');
        $admin->revokePermissionTo('cloud-sync.manage');

        Permission::findByName('cloud-sync.manage', 'web')?->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
