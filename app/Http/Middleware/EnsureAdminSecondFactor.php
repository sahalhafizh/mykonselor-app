<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSecondFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user?->isAdmin(), 403);
        if (! $user->two_factor_confirmed_at || ! $user->two_factor_secret) {
            return config('security.require_admin_mfa') ? to_route('admin.security') : $next($request);
        }
        $expected = $user->id.':'.hash('sha256', $user->two_factor_secret);
        if (! hash_equals($expected, (string) $request->session()->get('admin_mfa_verified', ''))) {
            return to_route('admin.security');
        }

        return $next($request);
    }
}
