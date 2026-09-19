<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\NormalizeProfileInput;
use App\Support\ResearchStudy;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        if (ResearchStudy::isResearch() && ! ResearchStudy::isOpen()) {
            return view('auth.registration-unavailable');
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        NormalizeProfileInput::apply($request);
        abort_if(ResearchStudy::isResearch() && ! ResearchStudy::isOpen(), 403, 'Pendaftaran penelitian belum dibuka atau telah berakhir.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'nim' => ['required', 'string', 'size:12', 'regex:/^[0-9]{12}$/', 'unique:users,nim'],
            'no_telp' => ['required', 'string', 'max:20', 'regex:/^(0|62)8[0-9]{8,11}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'max:72', 'confirmed', Rules\Password::defaults()],
            'consent' => ['accepted'],
            'semester' => ['required', 'integer', Rule::in(config('research.semesters'))],
            'research_consent' => ResearchStudy::isResearch() ? ['accepted'] : ['nullable'],
        ], [
            'semester.required' => 'Pilih semester Anda terlebih dahulu.',
            'semester.integer' => 'Semester harus berupa pilihan 7 atau 8.',
            'semester.in' => 'Pendaftaran tersedia untuk mahasiswa semester 7 atau 8.',
        ]);

        $normalizedPhone = preg_replace('/^0/', '62', $validated['no_telp']);

        $user = DB::transaction(function () use ($validated, $normalizedPhone) {
            $user = User::create([
                'name' => $validated['name'],
                'nim' => $validated['nim'],
                'no_telp' => $normalizedPhone,
                'email' => $validated['email'],
                'fakultas' => 'Fakultas Ilmu Komputer',
                'program_studi' => 'Teknik Informatika',
                'password' => Hash::make($validated['password']),
                'role' => 'mahasiswa',
                'status' => 'aktif',
                'data_consent_at' => now(),
            ]);

            // Capture the declared semester at registration, including local demo mode.
            // Research eligibility still uses the separately verified participation.
            $user->forceFill(['registration_semester' => (int) $validated['semester'], 'consent_version' => config('privacy.version'), 'consent_snapshot' => [
                'version' => config('privacy.version'), 'operator' => config('privacy.operator'),
                'contact_email' => config('privacy.contact_email'), 'retention_days' => config('privacy.retention_days'),
                'backup_retention_days' => config('privacy.backup_retention_days'),
                'purpose' => 'skrining, riwayat, rujukan, penelitian akademik sesuai kebijakan privasi',
            ]])->save();

            if (ResearchStudy::isResearch()) {
                $user->researchParticipations()->create([
                    'study_code' => config('research.study_code'), 'semester' => $validated['semester'],
                    'program_studi' => config('research.program'), 'protocol_fingerprint' => ResearchStudy::fingerprint(),
                    'consent_version' => config('research.consent_version'), 'consent_snapshot' => ResearchStudy::snapshot(),
                    'consented_at' => now(),
                ]);
            }

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect(route('dashboard'));
    }
}
