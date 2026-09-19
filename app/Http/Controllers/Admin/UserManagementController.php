<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SessionRevoker;
use App\Support\NormalizeProfileInput;
use App\Support\ResearchStudy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:aktif,nonaktif'], 'verification' => ['nullable', 'in:pending,verified'], 'semester' => ['nullable', 'in:7,8']]);
        $verifiedParticipant = fn ($q) => $q->where('study_code', config('research.study_code'))
            ->where('protocol_fingerprint', ResearchStudy::fingerprint())->whereNotNull('verified_at');
        $users = User::where('role', 'mahasiswa')
            ->with(['researchParticipations' => fn ($q) => $q->where('study_code', config('research.study_code'))])
            ->withCount('assessments')
            ->when($request->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('nim', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%")))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->verification === 'pending', fn ($q) => ResearchStudy::isResearch()
                ? $q->where(fn ($q) => $q->whereNull('identity_verified_at')->orWhereDoesntHave('researchParticipations', $verifiedParticipant))
                : $q->whereNull('identity_verified_at'))
            ->when($request->verification === 'verified', fn ($q) => ResearchStudy::isResearch()
                ? $q->whereNotNull('identity_verified_at')->whereHas('researchParticipations', $verifiedParticipant)
                : $q->whereNotNull('identity_verified_at'))
            ->when($request->semester, fn ($q) => $q->whereHas('researchParticipations', fn ($q) => $q->where('study_code', config('research.study_code'))->where('semester', $request->semester)))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        abort_unless($user->isMahasiswa(), 404);

        return view('admin.users.edit', ['user' => $user, 'participation' => ResearchStudy::participation($user)]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isMahasiswa(), 404);

        NormalizeProfileInput::apply($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'no_telp' => ['required', 'string', 'max:20', 'regex:/^(0|62)8[0-9]{8,11}$/'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name' => $validated['name'],
            'no_telp' => preg_replace('/^0/', '62', $validated['no_telp']),
            'email' => $validated['email'],
        ]);

        Log::info('Admin memperbarui profil pengguna', [
            'admin_id' => auth()->id(),
            'target_user_id' => $user->id,
        ]);

        return to_route('admin.users.edit', $user)->with('status', 'Profil pengguna berhasil diperbarui.');
    }

    public function toggleStatus(User $user, SessionRevoker $sessions): RedirectResponse
    {
        abort_unless($user->isMahasiswa(), 404);
        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->update(['status' => $newStatus]);
        if ($newStatus === 'nonaktif') {
            $sessions->revoke($user);
        }

        Log::info('Admin mengubah status akun', [
            'admin_id' => auth()->id(), 'target_user_id' => $user->id, 'new_status' => $newStatus,
        ]);

        return back()->with('status', "Status akun {$user->name} berhasil diperbarui.");
    }

    public function verifyIdentity(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isMahasiswa(), 404);
        $request->validate(['admin_password' => ['required', 'string', 'max:128', 'current_password:web']]);
        if (ResearchStudy::isResearch()) {
            $request->validate(['eligibility_confirmed' => ['accepted']]);
        }
        DB::transaction(function () use ($user, $request) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if (ResearchStudy::isResearch()) {
                $participation = ResearchStudy::participation($lockedUser);
                if (! ResearchStudy::isReady() || ! ResearchStudy::hasCurrentConsent($participation)
                    || $lockedUser->status !== 'aktif' || $lockedUser->program_studi !== config('research.program')
                    || ! in_array($participation->semester, config('research.semesters'), true)) {
                    throw ValidationException::withMessages(['eligibility_confirmed' => 'Data semester/persetujuan penelitian belum lengkap atau tidak sesuai sasaran.']);
                }
                $participation->update(['verified_at' => now(), 'verified_by' => $request->user()->id]);
            }
            $lockedUser->forceFill(['identity_verified_at' => now(), 'identity_verified_by' => $request->user()->id])->save();
        });
        Log::info('Admin memverifikasi identitas mahasiswa', ['admin_id' => $request->user()->id, 'target_user_id' => $user->id]);

        return back()->with('status', 'Identitas mahasiswa ditandai sudah diverifikasi.');
    }

    public function revokeResearch(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isMahasiswa(), 404);
        $request->validate(['admin_password' => ['required', 'string', 'max:128', 'current_password:web']]);
        DB::transaction(function () use ($user) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            ResearchStudy::participation($lockedUser)?->update(['verified_at' => null, 'verified_by' => null]);
        });
        Log::warning('Admin mencabut kelayakan peserta penelitian', ['admin_id' => $request->user()->id, 'target_user_id' => $user->id, 'study_code' => config('research.study_code')]);

        return back()->with('status', 'Verifikasi peserta dicabut. Pengisian dihentikan dan datanya tidak disertakan dalam rekap penelitian selama belum diverifikasi kembali.');
    }

    public function resetPassword(Request $request, User $user, SessionRevoker $sessions): RedirectResponse
    {
        abort_unless($user->isMahasiswa(), 404);
        $request->validate(['admin_password' => ['required', 'string', 'max:128', 'current_password:web']]);
        $tempPassword = str()->password(16);
        $user->forceFill(['password' => $tempPassword, 'must_change_password' => true, 'temporary_password_expires_at' => now()->addDay()])->save();
        $sessions->revoke($user);

        Log::info('Admin mereset password pengguna', [
            'admin_id' => auth()->id(), 'target_user_id' => $user->id,
        ]);

        return back()->with('status', "Password {$user->name} berhasil direset. Password sementara (berlaku 24 jam, wajib diganti): {$tempPassword}");
    }

    public function destroy(Request $request, User $user, SessionRevoker $sessions): RedirectResponse
    {
        abort_unless($user->isMahasiswa(), 404);
        $request->validate(['admin_password' => ['required', 'string', 'max:128', 'current_password:web']]);
        $sessions->revoke($user);
        $user->delete();

        Log::warning('Admin menghapus akun pengguna', [
            'admin_id' => auth()->id(), 'target_user_id' => $user->id,
        ]);

        return back()->with('status', "Akun {$user->name} berhasil dihapus.");
    }
}
