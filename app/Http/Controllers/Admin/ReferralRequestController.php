<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralRequestController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,dihubungi,selesai'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $referrals = ReferralRequest::query()
            ->with(['user', 'assessment.result', 'processedBy'])
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->whereHas('user', fn ($userQuery) => $userQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%"));
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'dihubungi' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = ReferralRequest::where('status', 'pending')->count();

        return view('admin.referrals.index', compact('referrals', 'pendingCount'));
    }

    public function show(ReferralRequest $referralRequest): View
    {
        $this->authorize('view', $referralRequest);
        $referralRequest->load(['user', 'assessment.result', 'processedBy']);

        return view('admin.referrals.show', compact('referralRequest'));
    }

    public function update(Request $request, ReferralRequest $referralRequest): RedirectResponse
    {
        $this->authorize('update', $referralRequest);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,dihubungi,selesai'],
        ]);

        $referralRequest->update([
            'status' => $validated['status'],
            'processed_by' => auth()->id(),
            'processed_at' => $validated['status'] === 'pending' ? null : now(),
        ]);

        return back()->with('status', 'Status pengajuan rujukan berhasil diperbarui.');
    }
}
