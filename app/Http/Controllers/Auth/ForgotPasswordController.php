<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    public function index()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Always send the same response regardless of whether the email exists
        // to prevent user enumeration attacks
        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            'If an account with that email exists, a password reset link has been sent.'
        );
    }
}
