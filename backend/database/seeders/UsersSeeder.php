<?php

namespace Database\Seeders;

use App\Modules\UserManagement\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch role objects explicitly by guard to avoid the "no role for guard web" error.
        // This is needed because our roles use guard_name='api' (set in config/permission.php).
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        $buyerRole = Role::where('name', 'buyer')->where('guard_name', 'api')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => Hash::make('password')]
        );
        if ($adminRole) $admin->syncRoles([$adminRole]);

        $buyer = User::firstOrCreate(
            ['email' => 'buyer@example.com'],
            ['name' => 'Buyer User', 'password' => Hash::make('password')]
        );
        if ($buyerRole) $buyer->syncRoles([$buyerRole]);
    }
}
