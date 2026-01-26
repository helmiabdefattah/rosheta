<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsDoctor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $doctor = $user->doctor;
        if (!$doctor) {
            abort(403, app()->getLocale() === 'ar' ? 'هذا القسم خاص بحساب الطبيب فقط.' : 'This area is for doctor accounts only.');
        }
        $request->attributes->set('doctor', $doctor);
        return $next($request);
    }
}
