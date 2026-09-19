<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ResearchStudy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResearchParticipationController extends Controller
{
    public function show(Request $request): View
    {
        abort_unless($request->user()->isMahasiswa(), 403);

        return view('research.participation', ['participation' => ResearchStudy::participation($request->user())]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isMahasiswa() && ResearchStudy::isResearch(), 403);
        abort_unless(ResearchStudy::isOpen(), 403, 'Periode pengisian penelitian belum dibuka atau telah berakhir.');
        $validated = $request->validate([
            'semester' => ['required', 'integer', Rule::in(config('research.semesters'))],
            'research_consent' => ['accepted'],
        ]);
        DB::transaction(function () use ($request, $validated) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $existing = ResearchStudy::participation($user);
            // A verified academic detail cannot be changed by a crafted student request.
            abort_if($existing?->verified_at && $existing->semester !== (int) $validated['semester'], 403);
            if (ResearchStudy::hasCurrentConsent($existing) && $existing->verified_at) {
                return;
            }
            $user->researchParticipations()->updateOrCreate(['study_code' => config('research.study_code')], [
                'semester' => $validated['semester'], 'program_studi' => config('research.program'),
                'protocol_fingerprint' => ResearchStudy::fingerprint(),
                'consent_version' => config('research.consent_version'), 'consent_snapshot' => ResearchStudy::snapshot(),
                'consented_at' => now(), 'verified_at' => null, 'verified_by' => null,
            ]);
        });

        return to_route('research.participation')->with('status', 'Persetujuan tercatat. Pengelola akan memverifikasi identitas dan semester Anda sebelum pengisian.');
    }
}
