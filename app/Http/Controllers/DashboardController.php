<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $lastAssessment = $user->assessments()->inCurrentMode()->where('status', 'completed')->latest('completed_at')->first();
        $inProgress = $user->assessments()
            ->resumableBy($user)
            ->whereHas('answers')
            ->latest('updated_at')
            ->first();
        $totalAssessments = $user->assessments()->inCurrentMode()->where('status', 'completed')->count();

        return view('dashboard', compact('lastAssessment', 'inProgress', 'totalAssessments'));
    }
}
