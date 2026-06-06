<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'api'; // Must match config/permission.php guard_name

        $permissions = [
            'manage-products',
            'manage-users',
            'view-orders',
            'place-orders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $adminRole->syncPermissions(Permission::where('guard_name', $guard)->get());

        $buyerRole = Role::firstOrCreate(['name' => 'buyer', 'guard_name' => $guard]);
        $buyerRole->syncPermissions(
            Permission::whereIn('name', ['place-orders', 'view-orders'])
                      ->where('guard_name', $guard)
                      ->get()
        );
    }
}
