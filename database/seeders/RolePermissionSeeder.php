<?php

namespace Database\Seeders;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::firstOrCreate(['name' => 'view users']);
        Permission::firstOrCreate(['name' => 'manage roles']);
        Permission::firstOrCreate(['name' => 'manage items']);
        Permission::firstOrCreate(['name' => 'manage categories']);

        // --- Role B: Customer (basic authenticated user, order placement only) ---
        // Note: replaces the old generic 'User' role
        Role::firstOrCreate(['name' => 'Customer']);

        // --- Role A: Product Manager (manages items, categories, orders) ---
        $roleProductManager = Role::firstOrCreate(['name' => 'Product Manager']);
        $roleProductManager->syncPermissions([
            'manage items',
            'manage categories',
        ]);

        // --- Website Administrator: Super Admin (full access) ---
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleSuperAdmin->givePermissionTo(Permission::all());

        // [1.1.1] Website Administrator account
        $admin = User::firstOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@inventory.local'),
        ], [
            'name'                => env('ADMIN_NAME', 'System Administrator'),
            'password'            => bcrypt(env('ADMIN_PASSWORD', 'Admin@1234!')),
            'password_changed_at' => now(),
        ]);
        $admin->assignRole($roleSuperAdmin);
        $this->seedPasswordHistory($admin);

        // [1.1.2] Product Manager account (Role A)
        $manager = User::firstOrCreate([
            'email' => env('MANAGER_EMAIL', 'manager@inventory.local'),
        ], [
            'name'                => env('MANAGER_NAME', 'Product Manager'),
            'password'            => bcrypt(env('MANAGER_PASSWORD', 'Manager@1234!')),
            'password_changed_at' => now(),
        ]);
        $manager->assignRole($roleProductManager);
        $this->seedPasswordHistory($manager);

        // [1.1.3] Customer account (Role B)
        $customer = User::firstOrCreate([
            'email' => env('CUSTOMER_EMAIL', 'customer@inventory.local'),
        ], [
            'name'                => env('CUSTOMER_NAME', 'Customer User'),
            'password'            => bcrypt(env('CUSTOMER_PASSWORD', 'Customer@1234!')),
            'password_changed_at' => now(),
        ]);
        $customer->assignRole('Customer');
        $this->seedPasswordHistory($customer);
    }

    private function seedPasswordHistory(User $user): void
    {
        // Record initial password in history so it cannot be immediately reused
        if ($user->wasRecentlyCreated) {
            PasswordHistory::create([
                'user_id'    => $user->id,
                'password'   => $user->password,
                'created_at' => now(),
            ]);
        }
    }
}
