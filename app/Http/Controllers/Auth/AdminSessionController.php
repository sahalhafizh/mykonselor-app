<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminSessionController extends Controller
{
    public function create()
    {
        return view('auth.admin-login');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'string', 'email', 'max:255'], 'password' => ['required', 'string', 'max:128']]);
        $email = Str::lower(trim($data['email']));
        $key = 'admin-login:'.hash('sha256', $email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Terlalu banyak percobaan. Coba kembali setelah satu menit.']);
        }
        $credentials = ['email' => $email, 'password' => $data['password'], 'role' => 'admin', 'status' => 'aktif'];
        $valid = Auth::guard('web')->validate($credentials);
        $user = Auth::guard('web')->getLastAttempted();
        if (! $valid
            || ($user->must_change_password && $user->temporary_password_expires_at?->isPast())
            || (app()->isProduction() && (in_array($data['password'], ['password', 'AdminDemoLokal2026!'], true) || str_ends_with($email, '.test')))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Email atau password admin tidak sesuai.']);
        }
        Auth::getProvider()->rehashPasswordIfRequired($user, $credentials);
        // Do not establish an authenticated session before an enrolled second factor succeeds.
        $request->session()->regenerate(true);
        $request->session()->forget(['admin_mfa_verified', 'admin_mfa_pending']);
        if ($user->two_factor_confirmed_at && $user->two_factor_secret) {
            $request->session()->put('admin_mfa_pending', ['id' => $user->id, 'expires' => now()->addMinutes(5)->timestamp,
                'password_hash' => hash('sha256', $user->password), 'throttle_key' => $key]);

            return to_route('admin.challenge');
        }
        RateLimiter::clear($key);
        Auth::login($user, false);

        return to_route(config('security.require_admin_mfa') ? 'admin.security' : 'admin.dashboard');
    }
}
