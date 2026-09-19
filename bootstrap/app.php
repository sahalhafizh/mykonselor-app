<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\EnsureAdminSecondFactor;
use App\Http\Middleware\EnsureStudentVerified;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RequirePasswordChange;
use App\Http\Middleware\TrustConfiguredProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(AddSecurityHeaders::class);
        $middleware->redirectGuestsTo(fn ($request) => route($request->is('admin/*') ? 'admin.login' : 'login'));
        $middleware->redirectUsersTo(fn ($request) => route($request->user()?->isAdmin() ? 'admin.dashboard' : 'dashboard'));
        $middleware->replace(TrustProxies::class, TrustConfiguredProxies::class);
        $middleware->trustHosts(at: fn () => [
            '^'.preg_quote(parse_url(config('app.url'), PHP_URL_HOST) ?: 'invalid-host', '/').'$',
        ], subdomains: false);
        $middleware->web(append: [
            AuthenticateSession::class,
            EnsureUserIsActive::class,
            RequirePasswordChange::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'admin.mfa' => EnsureAdminSecondFactor::class,
            'active' => EnsureUserIsActive::class,
            'student.verified' => EnsureStudentVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash(['current_password', 'admin_password', 'password', 'password_confirmation', 'code']);
    })->create();
