<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string', 'size:12', 'regex:/^[0-9]{12}$/'],
            'password' => ['required', 'string', 'max:128'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'nim' => $this->string('nim')->trim()->toString(),
            'password' => $this->input('password'),
            'status' => 'aktif',
            'role' => 'mahasiswa',
        ];

        $demoPasswordInProduction = app()->isProduction()
            && in_array($credentials['nim'], ['000000000000', '123456789012'], true)
            && $credentials['password'] === 'password';

        if ($demoPasswordInProduction || ! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'nim' => 'NIM atau password yang Anda masukkan tidak sesuai.',
            ]);
        }

        $user = Auth::user();
        if ($user->must_change_password && $user->temporary_password_expires_at?->isPast()) {
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages(['nim' => 'NIM atau password yang Anda masukkan tidak sesuai.']);
        }
        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'nim' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('nim')).'|'.$this->ip());
    }
}
