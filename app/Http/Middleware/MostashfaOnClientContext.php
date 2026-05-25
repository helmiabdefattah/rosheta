<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Marks requests from the MostashfaOn mobile app (API header) or embedded WebView (User-Agent).
 *
 * - API: Flutter sends header X-MostashfaOn-Client: mobile
 * - Web: WebView sets User-Agent containing MostashfaOnApp/...
 */
class MostashfaOnClientContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-MostashfaOn-Client') === 'mobile') {
            $request->attributes->set('mostashfaon_app', 'mobile');
        }

        $ua = $request->userAgent() ?? '';
        if (str_contains($ua, 'MostashfaOnApp')) {
            $request->attributes->set('mostashfaon_webview', true);
        }

        return $next($request);
    }
}
