<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates server-to-server calls from an external clinic-management
 * system. The caller sends its secret key in the `X-Integration-Key` header
 * (or `?integration_key=`); we resolve the owning clinic and attach it to the
 * request so downstream controllers are automatically scoped to that clinic.
 */
class VerifyIntegrationKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Integration-Key') ?: $request->query('integration_key');

        if (! $key) {
            return response()->json(['message' => 'Missing integration key.'], 401);
        }

        $clinic = Clinic::where('integration_key', $key)->first();

        if (! $clinic) {
            return response()->json(['message' => 'Invalid integration key.'], 401);
        }

        // Make the resolved clinic available to controllers.
        $request->attributes->set('integration_clinic', $clinic);

        return $next($request);
    }
}
