<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        // Force HTTPS in production
        if (app()->environment('production')) {
            $url->forceScheme('https');
        }

        // Share auth data with Inertia
        Inertia::share('auth', function () {
            return [
                'user' => Auth::user()
                    ? [
                        'id' => Auth::id(),
                        'name' => Auth::user()->name,
                        'email' => Auth::user()->email,
                    ]
                    : null,
            ];
        });
    }
}
