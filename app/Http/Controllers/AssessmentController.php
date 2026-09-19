<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Disease;
use App\Models\ReferralRequest;
use App\Support\ResearchStudy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function result(Assessment $assessment): View
    {
        $this->authorize('view', $assessment);

        if (ResearchStudy::participantResultsHidden($assessment)) {
            abort_unless($assessment->status === 'completed' && $assessment->result()->exists(), 404);

            return view('research.receipt', ['assessment' => $assessment]);
        }

        $assessment->load('result');
        abort_if(is_null($assessment->result), 404, 'Hasil belum tersedia untuk assessment ini.');

        return view('assessment.result', [
            'assessment' => $assessment,
            'result' => $assessment->result,
            'diseases' => Disease::all()->keyBy('cluster_key'),
        ]);
    }

    public function history(): View
    {
        $assessments = auth()->user()
            ->assessments()
            ->inCurrentMode()
            ->where('status', 'completed')
            ->when(! ResearchStudy::isResearch(), fn ($query) => $query->with('result'))
            ->latest('completed_at')
            ->paginate(10);

        return view('assessment.history', compact('assessments'));
    }

    public function referral(Assessment $assessment): View
    {
        $this->authorize('view', $assessment);

        abort_unless($assessment->status === 'completed' && $assessment->result()->exists(), 404, 'Hasil skrining belum tersedia.');
        $restricted = ResearchStudy::participantResultsHidden($assessment);

        return view('assessment.referral', [
            'assessment' => $assessment,
            'result' => $restricted ? null : $assessment->result,
            'diseases' => $restricted ? collect() : Disease::all()->keyBy('cluster_key'),
            'referralServices' => collect(config('referrals.services', [])),
            'existingRequest' => $assessment->referralRequest,
        ]);
    }

    public function storeReferralRequest(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('requestReferral', $assessment);
        abort_unless($assessment->status === 'completed' && $assessment->result()->exists(), 422, 'Hasil skrining belum tersedia.');

        $validated = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        $referral = ReferralRequest::firstOrCreate(
            ['user_id' => auth()->id(), 'assessment_id' => $assessment->id],
            ['status' => 'pending', 'catatan' => $validated['catatan'] ?? null],
        );

        if (! $referral->wasRecentlyCreated) {
            return back()->with('status', 'Pengajuan rujukan untuk hasil skrining ini sudah tercatat.');
        }

        return back()->with('status', 'Pengajuan kebutuhan konseling berhasil dikirim. Statusnya dapat dipantau melalui halaman ini.');
    }
}
