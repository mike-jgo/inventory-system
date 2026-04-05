<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $this->seed(RolePermissionSeeder::class);
});

// [1.1.1]
test('website administrator account exists with Super Admin role', function () {
    $admin = User::where('email', env('ADMIN_EMAIL', 'admin@inventory.local'))->first();

    expect($admin)->not->toBeNull();
    expect($admin->hasRole('Super Admin'))->toBeTrue();
});

// [1.1.2]
test('product manager account exists with Product Manager role', function () {
    $manager = User::where('email', env('MANAGER_EMAIL', 'manager@inventory.local'))->first();

    expect($manager)->not->toBeNull();
    expect($manager->hasRole('Product Manager'))->toBeTrue();
});

// [1.1.3]
test('customer account exists with Customer role', function () {
    $customer = User::where('email', env('CUSTOMER_EMAIL', 'customer@inventory.local'))->first();

    expect($customer)->not->toBeNull();
    expect($customer->hasRole('Customer'))->toBeTrue();
});
