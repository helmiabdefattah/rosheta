<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticatedClient
{
    /**
     * Patient (client) accounts only — same boundaries as client-only API features.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->user();

        if (! $auth instanceof Client) {
            return response()->json([
                'message' => 'This endpoint is only available for patient (client) accounts.',
            ], 403);
        }

        return $next($request);
    }
}
