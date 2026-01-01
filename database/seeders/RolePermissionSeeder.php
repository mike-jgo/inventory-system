<?php

namespace Database\Seeders;

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

        // Create roles and assign existing permissions
        $roleUser = Role::firstOrCreate(['name' => 'User']);

        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        // Super Admin gets all permissions
        $roleSuperAdmin->givePermissionTo(Permission::all());

        // Create Super Admin User with credentials from environment
        $admin = User::firstOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@inventory.local'),
        ], [
            'name' => env('ADMIN_NAME', 'System Administrator'),
            'password' => bcrypt(env('ADMIN_PASSWORD', 'change-me-immediately')),
        ]);
        $admin->assignRole($roleSuperAdmin);
    }
}
