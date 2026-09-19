<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // This middleware also runs on public routes. Guests have no account
        // status; route authentication middleware handles protected pages.
        if ($user !== null && $user->status !== 'aktif') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'nim' => 'NIM atau password yang Anda masukkan tidak sesuai.',
            ]);
        }

        return $next($request);
    }
}
