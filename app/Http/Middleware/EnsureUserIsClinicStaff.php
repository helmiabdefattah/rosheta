<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsClinicStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (! $user->managedClinic) {
            abort(403, app()->getLocale() === 'ar' ? 'هذا القسم لحساب موظفي العيادة فقط.' : 'This area is for clinic staff accounts only.');
        }

        $request->attributes->set('clinic', $user->managedClinic);

        return $next($request);
    }
}
