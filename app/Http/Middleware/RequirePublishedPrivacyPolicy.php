<?php

namespace App\Http\Middleware;

use App\Support\PrivacyPolicy;
use Closure;
use Illuminate\Http\Request;

class RequirePublishedPrivacyPolicy
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->isProduction() && ! PrivacyPolicy::isReady()) {
            return response()->view('auth.registration-unavailable', [], 503);
        }

        return $next($request);
    }
}
