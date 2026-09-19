<?php

namespace App\Http\Middleware;

use App\Support\ResearchStudy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user?->isMahasiswa(), 403);
        if (ResearchStudy::isResearch() && (! ResearchStudy::isOpen() || ! ResearchStudy::eligible($user))) {
            return to_route('research.participation');
        }
        if (config('security.require_student_verification') && ! $user->identity_verified_at) {
            return to_route('verification.notice');
        }

        return $next($request);
    }
}
