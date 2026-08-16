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
        // Deactivated by an admin while logged in: drop the session right away
        // instead of letting the workspace stay usable until it expires.
        if ($user->isSuspended()) {
            return $this->logoutSuspended($request);
        }
        $doctor = $user->doctor;
        if (!$doctor) {
            abort(403, app()->getLocale() === 'ar' ? 'هذا القسم خاص بحساب الطبيب فقط.' : 'This area is for doctor accounts only.');
        }
        $request->attributes->set('doctor', $doctor);
        return $next($request);
    }

    /** Log a deactivated account out and send it back to the login screen. */
    protected function logoutSuspended(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => app()->getLocale() === 'ar'
                ? 'حسابك غير مفعّل. يرجى التواصل مع الإدارة لتفعيل الحساب.'
                : 'Your account is not active. Please contact the administration to activate it.',
        ]);
    }
}
