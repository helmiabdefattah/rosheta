<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectLaboratoryOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user is a laboratory owner
            if ($user->laboratory_id) {
                // Redirect to laboratory dashboard if accessing admin dashboard or root admin
                // But don't redirect if already going to laboratory dashboard to avoid loops
                if (($request->is('admin') || 
                     $request->is('admin/dashboard') || 
                     $request->routeIs('admin.dashboard')) &&
                    !$request->is('admin/laboratory/dashboard') &&
                    !$request->routeIs('laboratories.dashboard')) {
                    return redirect()->route('laboratories.dashboard');
                }
            }
        }

        return $next($request);
    }
}
