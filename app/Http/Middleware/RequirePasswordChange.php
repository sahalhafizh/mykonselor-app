<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->must_change_password) {
            if ($user->temporary_password_expires_at?->isPast()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return to_route('login')->withErrors(['nim' => 'Password sementara kedaluwarsa. Hubungi pengelola untuk pemulihan akun.']);
            }
            if (! $request->routeIs('account.password.*', 'logout')) {
                return to_route('account.password.edit');
            }
        }

        return $next($request);
    }
}
