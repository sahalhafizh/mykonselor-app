<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SessionRevoker;
use App\Support\NormalizeProfileInput;
use App\Support\ResearchStudy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $this->student($request);

        return view('profile.show', ['user' => $user, 'participation' => ResearchStudy::participation($user)]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->student($request);

        NormalizeProfileInput::apply($request);

        $validated = $request->validateWithBag('profileUpdate', [
            'name' => ['missing'],
            'no_telp' => ['required', 'string', 'max:20', 'regex:/^(0|62)8[0-9]{8,11}$/'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.missing' => 'Nama yang terdaftar tidak dapat diubah melalui profil.',
        ]);

        $user->update([
            'no_telp' => preg_replace('/^0/', '62', $validated['no_telp']),
            'email' => $validated['email'],
        ]);

        return to_route('profile.show')->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request, SessionRevoker $sessions): RedirectResponse
    {
        $user = $this->student($request);

        $validated = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'string', 'max:128', 'current_password:web'],
            'password' => ['required', 'string', 'max:72', 'confirmed', 'different:current_password', Rules\Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);
        $sessions->revoke($user, $request->session()->getId());
        $request->session()->regenerate(true);

        Log::info('Pengguna mengubah password akun', [
            'user_id' => $user->id,
        ]);

        return to_route('profile.show')->with('status', 'Password berhasil diperbarui. Sesi di perangkat lain telah dicabut.');
    }

    private function student(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isMahasiswa(), 404);

        return $user;
    }
}
