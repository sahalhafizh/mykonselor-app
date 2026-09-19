<?php

namespace App\Providers;

use App\Http\Middleware\EnsureAdminSecondFactor;
use App\Http\Middleware\EnsureStudentVerified;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RequirePasswordChange;
use App\Models\ReferralRequest;
use Illuminate\Pagination\Paginator;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

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
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Password::defaults(
            fn () => Password::min(12)->letters()->mixedCase()->numbers()->rules([
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('Password maksimal 72 byte. Gunakan password yang lebih pendek.');
                    }
                },
            ])
        );
        Livewire::addPersistentMiddleware([
            EnsureUserIsActive::class,
            EnsureStudentVerified::class,
            RequirePasswordChange::class,
            EnsureUserIsAdmin::class,
            EnsureAdminSecondFactor::class,
            AuthenticateSession::class,
        ]);

        View::composer('components.layouts.admin', function ($view) {
            $view->with(
                'adminPendingReferralCount',
                auth()->check() && auth()->user()->isAdmin()
                    ? ReferralRequest::where('status', 'pending')->count()
                    : 0,
            );
        });
    }
}
