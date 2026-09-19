<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SessionRevoker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class RequiredPasswordController extends Controller
{
    public function edit()
    {
        return view('auth.required-password');
    }

    public function update(Request $request, SessionRevoker $sessions)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'max:128', 'current_password:web'],
            'password' => ['required', 'string', 'max:72', 'confirmed', 'different:current_password', Password::defaults()],
        ]);
        $user = $request->user();
        $user->forceFill(['password' => $data['password'], 'must_change_password' => false, 'temporary_password_expires_at' => null])->save();
        $sessions->revoke($user, $request->session()->getId());
        $request->session()->regenerate(true);

        return to_route($user->isAdmin() ? 'admin.dashboard' : 'dashboard')->with('status', 'Password pribadi berhasil disimpan.');
    }
}
