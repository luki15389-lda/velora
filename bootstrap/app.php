<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\RedirectIfNotBackoffice;
use App\Http\Middleware\UseAdminGuard;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // "admin-guard" runs first so every "auth"/"guest" check inside
            // routes/admin.php resolves against the "admin" guard, not "web".
            //
            // env(), not config() — this runs before Laravel's config system
            // is booted, so config('app.admin_path') would fail here (it did).
            Route::middleware(['web', UseAdminGuard::class])
                ->prefix(env('ADMIN_PATH', 'admin'))
                ->as('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'backoffice' => RedirectIfNotBackoffice::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            $adminPath = env('ADMIN_PATH', 'admin');

            return $request->is($adminPath, "{$adminPath}/*") ? route('admin.login') : route('login');
        });

        $middleware->redirectUsersTo(function ($request) {
            $adminPath = env('ADMIN_PATH', 'admin');

            return $request->is($adminPath, "{$adminPath}/*") ? route('admin.dashboard') : route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();