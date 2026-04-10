<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // [2.4.1] Never expose stack traces or debug info to users
        $exceptions->dontReportDuplicates();

        // [2.4.6] Log unauthenticated access attempts
        // NOTE: AuthenticationException is in Laravel's $dontReport, so report() never fires.
        // Logging is done in render() instead; returning null lets the default login redirect proceed.
        $exceptions->render(function (AuthenticationException $e, \Illuminate\Http\Request $request) {
            activity()
                ->withProperties([
                    'url' => $request->url(),
                    'ip'  => $request->ip(),
                ])
                ->log('unauthenticated_access');

            return null;
        });

        // [2.4.5] Log all input validation failures
        // NOTE: ValidationException is in Laravel's $internalDontReport, so report() never fires.
        // Logging is done here in render() instead; returning null lets default handling proceed.
        // The login route is excluded because login_failed is already logged there explicitly.
        $exceptions->render(function (ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->routeIs('login.attempt')) {
                return null;
            }

            $sensitiveFields = ['password', 'current_password', 'password_confirmation', 'token'];
            $fields = array_keys(array_diff_key($e->errors(), array_flip($sensitiveFields)));

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'url'    => $request->url(),
                    'fields' => $fields,
                ])
                ->log('validation_failed');

            return null; // let default redirect-back-with-errors proceed
        });

        // [2.4.2] Render error pages through Inertia to stay within the SPA
        // [2.4.7] Log access control failures (403)
        // NOTE: HttpException is in Laravel's $internalDontReport, so report() never fires.
        // Both logging and rendering are handled here together.
        $exceptions->render(function (HttpException $e, \Illuminate\Http\Request $request) {
            $status = $e->getStatusCode();

            if ($status === 403) {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties(['url' => $request->url()])
                    ->log('access_denied');
            }

            if (in_array($status, [403, 404, 500])) {
                return Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }
        });
    })->create();
