<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies;

class TrustConfiguredProxies extends TrustProxies
{
    protected function proxies()
    {
        return config('security.trusted_proxies', []);
    }
}
