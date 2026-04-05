<?php

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Spatie permission cache must be cleared between tests
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
});

function makeUser(string $password = 'OldPass@1234!', ?string $passwordChangedAt = null): User
{
    return User::factory()->create([
        'password'            => Hash::make($password),
        'password_changed_at' => $passwordChangedAt,
    ]);
}

// ---------------------------------------------------------------------------
// [2.1.11] Password age enforcement
// ---------------------------------------------------------------------------

test('blocks password change when password is less than 24 hours old', function () {
    $user = makeUser(passwordChangedAt: now()->subHours(2)->toDateTimeString());

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password'      => 'OldPass@1234!',
        'password'              => 'NewPass@1234!',
        'password_confirmation' => 'NewPass@1234!',
    ]);

    $response->assertSessionHasErrors('password');
    expect(User::find($user->id)->password_changed_at->toDateTimeString())
        ->toBe($user->password_changed_at->toDateTimeString()); // unchanged
});

test('allows password change when password is older than 24 hours', function () {
    $user = makeUser(passwordChangedAt: now()->subHours(25)->toDateTimeString());

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password'      => 'OldPass@1234!',
        'password'              => 'NewPass@1234!',
        'password_confirmation' => 'NewPass@1234!',
    ]);

    $response->assertSessionHasNoErrors();
    expect(Hash::check('NewPass@1234!', User::find($user->id)->password))->toBeTrue();
});

test('blocks password change when password_changed_at is null', function () {
    $user = makeUser(passwordChangedAt: null);

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password'      => 'OldPass@1234!',
        'password'              => 'NewPass@1234!',
        'password_confirmation' => 'NewPass@1234!',
    ]);

    $response->assertSessionHasErrors('password');
});

// ---------------------------------------------------------------------------
// [2.1.10] Password re-use prevention
// ---------------------------------------------------------------------------

test('blocks reuse of a recent password', function () {
    $user = makeUser('OldPass@1234!', now()->subHours(25)->toDateTimeString());

    // Seed one history entry with the password we will try to reuse
    PasswordHistory::create([
        'user_id'    => $user->id,
        'password'   => Hash::make('ReusedPass@1234!'),
        'created_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password'      => 'OldPass@1234!',
        'password'              => 'ReusedPass@1234!',
        'password_confirmation' => 'ReusedPass@1234!',
    ]);

    $response->assertSessionHasErrors('password');
});

test('allows a password not in recent history', function () {
    $user = makeUser('OldPass@1234!', now()->subHours(25)->toDateTimeString());

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password'      => 'OldPass@1234!',
        'password'              => 'BrandNew@1234!',
        'password_confirmation' => 'BrandNew@1234!',
    ]);

    $response->assertSessionHasNoErrors();
});

// ---------------------------------------------------------------------------
// [2.1.13] Re-authentication (current_password required)
// ---------------------------------------------------------------------------

test('blocks password change when current password is wrong', function () {
    $user = makeUser('OldPass@1234!', now()->subHours(25)->toDateTimeString());

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password'      => 'WrongPass@1234!',
        'password'              => 'NewPass@1234!',
        'password_confirmation' => 'NewPass@1234!',
    ]);

    $response->assertSessionHasErrors('current_password');
});

// ---------------------------------------------------------------------------
// [2.1.5] Password complexity on change
// ---------------------------------------------------------------------------

test('blocks weak new password on change', function () {
    $user = makeUser('OldPass@1234!', now()->subHours(25)->toDateTimeString());

    $response = $this->actingAs($user)->put(route('password.update'), [
        'current_password'      => 'OldPass@1234!',
        'password'              => 'weakpassword',
        'password_confirmation' => 'weakpassword',
    ]);

    $response->assertSessionHasErrors('password');
});
