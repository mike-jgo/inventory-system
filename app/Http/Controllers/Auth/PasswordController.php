<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PasswordController extends Controller
{
    public function edit()
    {
        return Inertia::render('Auth/ChangePassword');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password'         => ['required', 'confirmed', 'max:128', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        // fresh() after validation so we read the absolute latest state from DB
        $user = Auth::user()->fresh();

        // [2.1.11] Password must be at least 1 day old before it can be changed.
        $tooRecent = is_null($user->password_changed_at)
            || $user->password_changed_at->gt(now()->subDay());

        if ($tooRecent) {
            throw ValidationException::withMessages([
                'password' => 'Your password must be at least 1 day old before it can be changed.',
            ]);
        }

        // [2.1.10] New password must not match any of the last 5 passwords
        $recentHashes = $user->passwordHistories()->limit(5)->pluck('password');
        foreach ($recentHashes as $oldHash) {
            if (Hash::check($request->password, $oldHash)) {
                throw ValidationException::withMessages([
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

        // Keep only the last 5 entries per user
        $ids = $user->passwordHistories()->pluck('id')->slice(5);
        if ($ids->isNotEmpty()) {
            PasswordHistory::whereIn('id', $ids)->delete();
        }

        $user->password             = $request->password;
        $user->password_changed_at  = now();
        $user->save();

        // [2.4.3] Log password change
        activity()
            ->causedBy($user)
            ->log('password_changed');

        return back()->with('success', 'Password changed successfully.');
    }
}
