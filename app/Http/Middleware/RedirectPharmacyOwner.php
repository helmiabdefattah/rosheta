<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectPharmacyOwner
{
    /**
     * Pharmacy staff should use the pharmacy dashboard, not the admin dashboard.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->pharmacy_id) {
                if (($request->is('admin')
                    || $request->is('admin/dashboard')
                    || $request->routeIs('admin.dashboard'))
                    && !$request->routeIs('pharmacies.dashboard')) {
                    return redirect()->route('pharmacies.dashboard');
                }
            }
        }

        return $next($request);
    }
}
