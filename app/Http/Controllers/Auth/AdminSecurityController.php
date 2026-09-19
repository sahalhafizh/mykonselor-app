<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AdminSecurityController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $secret = null;
        if (! $user->two_factor_confirmed_at) {
            $setup = $request->session()->get('mfa_setup');
            if (! is_array($setup) || $setup['expires'] < time() || $setup['id'] !== $user->id) {
                $setup = ['secret' => (new Google2FA)->generateSecretKey(32), 'expires' => now()->addMinutes(10)->timestamp, 'id' => $user->id];
                $request->session()->put('mfa_setup', $setup);
            }
            $secret = $setup['secret'];
        }
        $verified = $user->two_factor_secret && hash_equals($user->id.':'.hash('sha256', $user->two_factor_secret), (string) $request->session()->get('admin_mfa_verified', ''));

        return view('auth.admin-security', compact('user', 'secret', 'verified'));
    }

    public function confirm(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'string', 'max:128', 'current_password:web'], 'code' => ['required', 'string', 'regex:/^[0-9]{6}$/']]);
        $setup = $request->session()->get('mfa_setup');
        $user = DB::transaction(function () use ($request, $setup, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $secret = $user->two_factor_secret;
            if (! $user->two_factor_confirmed_at) {
                if (! is_array($setup) || $setup['expires'] < time() || $setup['id'] !== $user->id) {
                    throw ValidationException::withMessages(['code' => 'Pengaturan kedaluwarsa. Muat ulang halaman.']);
                }
                $secret = $setup['secret'];
            }
            $step = (new Google2FA)->verifyKeyNewer($secret, $data['code'], $user->two_factor_last_step ?? 0, 1);
            if ($step === false) {
                throw ValidationException::withMessages(['code' => 'Kode tidak sesuai atau sudah digunakan.']);
            }
            $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => $user->two_factor_confirmed_at ?? now(), 'two_factor_last_step' => $step])->save();

            return $user;
        });
        Auth::setUser($user);
        $request->session()->forget('mfa_setup');
        $request->session()->regenerate(true);
        $request->session()->put('admin_mfa_verified', $user->id.':'.hash('sha256', $user->two_factor_secret));

        return to_route('admin.dashboard')->with('status', 'Verifikasi dua langkah berhasil.');
    }

    public function challenge(Request $request)
    {
        $pending = $request->session()->get('admin_mfa_pending');
        if (! is_array($pending) || ($pending['expires'] ?? 0) < time()) {
            $request->session()->forget('admin_mfa_pending');

            return to_route('admin.login');
        }

        return view('auth.admin-challenge');
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'regex:/^[0-9]{6}$/']]);
        $pending = $request->session()->get('admin_mfa_pending');
        if (! is_array($pending) || ($pending['expires'] ?? 0) < time()) {
            return to_route('admin.login');
        }
        $key = 'mfa:'.hash('sha256', $pending['id'].'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['code' => 'Terlalu banyak percobaan kode. Tunggu satu menit.']);
        }
        RateLimiter::hit($key, 60);
        $user = DB::transaction(function () use ($data, $pending) {
            $user = User::whereKey($pending['id'])->where('role', 'admin')->where('status', 'aktif')->lockForUpdate()->first();
            if (! $user || ! $user->two_factor_confirmed_at || ! $user->two_factor_secret
                || ! hash_equals($pending['password_hash'], hash('sha256', $user->password))) {
                throw ValidationException::withMessages(['code' => 'Verifikasi kedaluwarsa. Silakan masuk kembali.']);
            }
            $step = (new Google2FA)->verifyKeyNewer($user->two_factor_secret, $data['code'], $user->two_factor_last_step ?? 0, 1);
            if ($step === false) {
                throw ValidationException::withMessages(['code' => 'Kode tidak sesuai atau sudah digunakan.']);
            }
            $user->forceFill(['two_factor_last_step' => $step])->save();

            return $user;
        });
        RateLimiter::clear($key);
        RateLimiter::clear($pending['throttle_key']);
        $request->session()->forget('admin_mfa_pending');
        Auth::login($user, false);
        $request->session()->regenerate(true);
        $request->session()->put('admin_mfa_verified', $user->id.':'.hash('sha256', $user->two_factor_secret));

        return to_route('admin.dashboard');
    }
}
