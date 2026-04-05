<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    public function attempt(LoginRequest $request)
    {
        // Validates credentials + enforces rate limiting (5 attempts before lockout)
        $user = $request->validateCredentials();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // [2.4.3] Log successful login
        activity()
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip()])
            ->log('login');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        // [2.4.3] Log logout before session is destroyed
        activity()
            ->causedBy(Auth::user())
            ->log('logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
