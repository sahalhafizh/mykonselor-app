<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\ReferralRequest;
use App\Models\User;
use App\Services\ResearchReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(ResearchReportService $reports): View
    {
        $totalUsers = User::where('role', 'mahasiswa')->count();

        $localNow = now(config('app.display_timezone'));
        $totalScreeningsToday = Assessment::inCurrentMode()->where('status', 'completed')->whereBetween('completed_at', [
            $localNow->copy()->startOfDay()->utc(), $localNow->copy()->endOfDay()->utc(),
        ])->count();

        $totalAssessmentsThisMonth = Assessment::inCurrentMode()->where('status', 'completed')
            ->whereBetween('completed_at', [$localNow->copy()->startOfMonth()->utc(), $localNow->copy()->endOfMonth()->utc()])
            ->count();

        $pendingReferrals = ReferralRequest::where('status', 'pending')->count();

        $summary = $reports->summary();
        $metadata = $reports->metadata();
        $distribution = $summary['distribution'];

        $attentionQuery = $reports->selected()->whereHas('result', fn ($q) => $q->where('highest_severity', 'berat'));
        $attentionCount = (clone $attentionQuery)->count();
        $needsAttention = $attentionQuery
            ->with(['user', 'result'])
            ->latest('completed_at')
            ->limit(10)
            ->get();

        $trend = Assessment::inCurrentMode()->where('status', 'completed')
            ->where('completed_at', '>=', now()->subWeeks(8))
            ->get(['completed_at'])
            ->groupBy(fn (Assessment $assessment) => $assessment->completed_at?->copy()->timezone(config('app.display_timezone'))->format('o-W'))
            ->map(fn ($items, $week) => (object) ['week' => $week, 'total' => $items->count()])
            ->sortBy('week')
            ->values();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalScreeningsToday', 'totalAssessmentsThisMonth', 'pendingReferrals',
            'distribution', 'needsAttention', 'attentionCount', 'trend', 'summary', 'metadata'
        ));
    }
}
