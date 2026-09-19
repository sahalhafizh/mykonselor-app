<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectAssessmentAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        return $request->user()?->isAdmin()
            ? app(EnsureAdminSecondFactor::class)->handle($request, $next)
            : $next($request);
    }
}
