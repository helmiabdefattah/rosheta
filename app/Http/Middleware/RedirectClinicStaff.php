<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectClinicStaff
{
    /**
     * Clinic staff (clinic manager user) should use the clinic dashboard, not the admin area.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->managedClinic) {
                if (($request->is('admin')
                    || $request->is('admin/dashboard')
                    || $request->routeIs('admin.dashboard'))
                    && ! $request->routeIs('clinic.dashboard')) {
                    return redirect()->route('clinic.dashboard');
                }
            }
        }

        return $next($request);
    }
}
