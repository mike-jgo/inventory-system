<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $this->seed(RolePermissionSeeder::class);
});

// [2.4.3, 2.4.6] Successful login is logged
test('successful login creates a login activity log entry', function () {
    $user = User::factory()->create([
        'email'    => 'logme@example.com',
        'password' => Hash::make('Login@1234!'),
    ]);

    $this->post(route('login.attempt'), [
        'email'    => 'logme@example.com',
        'password' => 'Login@1234!',
    ]);

    expect(
        Activity::where('log_name', 'default')
            ->where('description', 'login')
            ->where('causer_id', $user->id)
            ->exists()
    )->toBeTrue();
});

// [2.4.6] Failed login is logged
test('failed login creates a login_failed activity log entry', function () {
    $user = User::factory()->create([
        'email'    => 'failme@example.com',
        'password' => Hash::make('Correct@1234!'),
    ]);

    $this->post(route('login.attempt'), [
        'email'    => 'failme@example.com',
        'password' => 'WrongPassword1!',
    ]);

    expect(
        Activity::where('log_name', 'default')
            ->where('description', 'login_failed')
            ->where('causer_id', $user->id)
            ->exists()
    )->toBeTrue();
});

// [2.4.3] Logout is logged
test('logout creates a logout activity log entry', function () {
    $user = User::factory()->create([
        'email'    => 'byeme@example.com',
        'password' => Hash::make('Logout@1234!'),
    ]);

    $this->actingAs($user)->post(route('logout'));

    expect(
        Activity::where('log_name', 'default')
            ->where('description', 'logout')
            ->where('causer_id', $user->id)
            ->exists()
    )->toBeTrue();
});

// [2.4.3] Password change is logged
test('password change creates a password_changed activity log entry', function () {
    $user = User::factory()->create([
        'password'             => Hash::make('OldPass@1234!'),
        'password_changed_at'  => now()->subDays(2),
    ]);

    $this->actingAs($user)->put(route('password.update'), [
        'current_password'      => 'OldPass@1234!',
        'password'              => 'NewPass@5678!',
        'password_confirmation' => 'NewPass@5678!',
    ]);

    expect(
        Activity::where('log_name', 'default')
            ->where('description', 'password_changed')
            ->where('causer_id', $user->id)
            ->exists()
    )->toBeTrue();
});

// [2.4.7] Accessing a forbidden route creates an access_denied log entry
test('accessing a forbidden route creates an access_denied activity log entry', function () {
    $customer = customerUser();

    $this->actingAs($customer)->get(route('activity-log.index'));

    expect(
        Activity::where('log_name', 'default')
            ->where('description', 'access_denied')
            ->where('causer_id', $customer->id)
            ->exists()
    )->toBeTrue();
});

// [2.4.5] Submitting invalid data creates a validation_failed log entry
test('submitting invalid data creates a validation_failed activity log entry', function () {
    $manager = managerUser();

    $this->actingAs($manager)->post(route('items.store'), [
        'name'        => '',   // missing required field
        'category_id' => 1,
        'quantity'    => 10,
        'price'       => 9.99,
    ]);

    expect(
        Activity::where('log_name', 'default')
            ->where('description', 'validation_failed')
            ->exists()
    )->toBeTrue();
});

// [2.4.2] 403 errors render the Inertia Error page (not raw Laravel HTML)
test('403 response is rendered through Inertia not raw HTML', function () {
    $customer = customerUser();

    $response = $this->actingAs($customer)
        ->get(route('activity-log.index'));

    $response->assertStatus(403);

    // Inertia responses are always JSON for XHR or HTML containing the page component
    // When Inertia renders a page, the component name appears in the response body
    $response->assertSee('Error');
});

// [2.4.2] 404 response is rendered through Inertia
test('404 response is rendered through Inertia not raw HTML', function () {
    $admin = adminUser();

    $response = $this->actingAs($admin)
        ->get('/this-route-does-not-exist-xyz');

    $response->assertStatus(404);
    $response->assertSee('Error');
});
