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

        // Create Super Admin User
        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($roleSuperAdmin);

        // Create Standard User
        $user = User::firstOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'Standard User',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($roleUser);
    }
}
