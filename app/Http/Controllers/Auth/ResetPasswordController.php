<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;

class ResetPasswordController extends Controller
{
    public function index(Request $request, string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // [2.1.10] Check new password against last 5 used passwords
                $recentHashes = $user->passwordHistories()->limit(5)->pluck('password');
                foreach ($recentHashes as $oldHash) {
                    if (Hash::check($password, $oldHash)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'password' => 'You cannot reuse a recent password.',
                        ]);
                    }
                }

                // Archive current password before overwriting
                PasswordHistory::create([
                    'user_id'    => $user->id,
                    'password'   => $user->password,
                    'created_at' => now(),
                ]);

                // Keep only last 5 entries
                $ids = $user->passwordHistories()->pluck('id')->slice(5);
                if ($ids->isNotEmpty()) {
                    PasswordHistory::whereIn('id', $ids)->delete();
                }

                $user->forceFill([
                    'password'            => Hash::make($password),
                    'password_changed_at' => now(),
                ])->save();

                // [2.4.3] Log password reset
                activity()
                    ->causedBy($user)
                    ->log('password_reset');
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password reset successfully. You may now log in.');
        }

        return back()->withErrors(['email' => 'This password reset link is invalid or has expired.']);
    }
}
