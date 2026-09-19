<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * Add browser security controls that do not depend on an external service.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // Vite HMR needs a development WebSocket. The built application uses
        // only local scripts, including Livewire's CSP-compatible build.
        if (app()->isProduction() || ! Vite::isRunningHot()) {
            $nonce = Vite::cspNonce();
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "base-uri 'self'",
                "object-src 'none'",
                "frame-ancestors 'self'",
                "form-action 'self'",
                "script-src 'self' 'nonce-{$nonce}'",
                // Bootstrap and Livewire use inline styles for dynamic layout.
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: https:",
                "font-src 'self' data:",
                "connect-src 'self'",
            ]));
        }

        if (app()->isProduction() && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        if (str_contains($response->headers->get('Content-Type', ''), 'text/html')
            || ($request->hasSession() && $request->user())) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
