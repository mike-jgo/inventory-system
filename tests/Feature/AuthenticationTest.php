<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    RateLimiter::clear('test@example.com|127.0.0.1');
});

// [2.1.1] Authentication required for all protected pages
test('unauthenticated user is redirected to login when accessing dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('unauthenticated user is redirected to login when accessing orders', function () {
    $this->get(route('orders.index'))->assertRedirect(route('login'));
});

// [2.1.2] Auth controls fail securely — no unhandled exception (500), redirects back with errors
test('failed login does not expose an unhandled exception', function () {
    $this->post(route('login.attempt'), [
        'email'    => 'nobody@example.com',
        'password' => 'WrongPassword1!',
    ])->assertRedirect()->assertSessionHasErrors('login');
});

// [2.1.3] Passwords stored as hashed
test('password is stored as a bcrypt hash not plaintext', function () {
    $this->post(route('register.store'), [
        'name'                  => 'Test User',
        'email'                 => 'hash@example.com',
        'password'              => 'Secure@1234!',
        'password_confirmation' => 'Secure@1234!',
    ]);

    $user = User::where('email', 'hash@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->password)->not->toBe('Secure@1234!');
    expect(Hash::check('Secure@1234!', $user->password))->toBeTrue();
});

// [2.1.4] Generic error message — error key is 'login', not 'email' or 'password'
test('failed login returns a generic error on the login key not the email key', function () {
    $this->post(route('login.attempt'), [
        'email'    => 'nobody@example.com',
        'password' => 'WrongPassword1!',
    ])->assertSessionHasErrors('login')
      ->assertSessionMissing('errors.email')
      ->assertSessionMissing('errors.password');
});

// [2.1.5] Password complexity on registration
test('registration rejects a password missing uppercase', function () {
    $this->post(route('register.store'), [
        'name'                  => 'Test',
        'email'                 => 'weak1@example.com',
        'password'              => 'nouppercase1!',
        'password_confirmation' => 'nouppercase1!',
    ])->assertSessionHasErrors('password');
});

test('registration rejects a password missing a number', function () {
    $this->post(route('register.store'), [
        'name'                  => 'Test',
        'email'                 => 'weak2@example.com',
        'password'              => 'NoNumber!abc',
        'password_confirmation' => 'NoNumber!abc',
    ])->assertSessionHasErrors('password');
});

test('registration rejects a password missing a special character', function () {
    $this->post(route('register.store'), [
        'name'                  => 'Test',
        'email'                 => 'weak3@example.com',
        'password'              => 'NoSpecial1234',
        'password_confirmation' => 'NoSpecial1234',
    ])->assertSessionHasErrors('password');
});

// [2.1.6] Minimum password length
test('registration rejects a password shorter than 8 characters', function () {
    $this->post(route('register.store'), [
        'name'                  => 'Test',
        'email'                 => 'short@example.com',
        'password'              => 'Ab1!',
        'password_confirmation' => 'Ab1!',
    ])->assertSessionHasErrors('password');
});

// [2.1.8] Account lockout after 5 failed attempts
test('account is locked after 5 failed login attempts', function () {
    $email = 'lockout@example.com';

    User::factory()->create([
        'email'    => $email,
        'password' => Hash::make('Correct@1234!'),
    ]);

    // Clear any existing rate limit for this key
    RateLimiter::clear("{$email}|127.0.0.1");

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.attempt'), [
            'email'    => $email,
            'password' => 'WrongPassword1!',
        ]);
    }

    // 6th attempt should be throttled
    $this->post(route('login.attempt'), [
        'email'    => $email,
        'password' => 'Correct@1234!',
    ])->assertSessionHasErrors('login');
});

test('lockout message is time-limited not a permanent ban', function () {
    $email = 'lockout2@example.com';
    RateLimiter::clear("{$email}|127.0.0.1");

    User::factory()->create([
        'email'    => $email,
        'password' => Hash::make('Correct@1234!'),
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.attempt'), [
            'email'    => $email,
            'password' => 'WrongPassword1!',
        ]);
    }

    // Locked — error should contain 'seconds' or 'minutes' indicating temporary
    $response = $this->post(route('login.attempt'), [
        'email'    => $email,
        'password' => 'WrongPassword1!',
    ]);

    $errors = session('errors')?->get('login') ?? [];
    $message = implode(' ', $errors);
    expect(str_contains($message, 'seconds') || str_contains($message, 'minutes'))->toBeTrue();
});

// [2.1.9] Password reset requires a valid token
test('password reset with an invalid token is rejected', function () {
    $user = User::factory()->create(['email' => 'reset@example.com']);

    $this->post(route('password.store'), [
        'token'                 => 'invalid-token-xyz',
        'email'                 => 'reset@example.com',
        'password'              => 'NewSecure@1234!',
        'password_confirmation' => 'NewSecure@1234!',
    ])->assertSessionHasErrors('email');
});

// [2.1.12] Last login tracking
test('last_login_at is updated on successful login', function () {
    $user = User::factory()->create([
        'email'    => 'track@example.com',
        'password' => Hash::make('Track@1234!'),
    ]);

    expect($user->last_login_at)->toBeNull();

    $this->post(route('login.attempt'), [
        'email'    => 'track@example.com',
        'password' => 'Track@1234!',
    ]);

    expect(User::find($user->id)->last_login_at)->not->toBeNull();
});

test('previous last_login_at is stored in session before being overwritten', function () {
    $previousLogin = now()->subDay();

    $user = User::factory()->create([
        'email'         => 'prev@example.com',
        'password'      => Hash::make('Prev@1234!'),
        'last_login_at' => $previousLogin,
    ]);

    $this->post(route('login.attempt'), [
        'email'    => 'prev@example.com',
        'password' => 'Prev@1234!',
    ]);

    expect(session('previous_login_at'))
        ->toBe($previousLogin->toDateTimeString());
});
