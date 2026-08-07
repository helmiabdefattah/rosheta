<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the clinic (design) system by role. A user is a "doctor" when they own
 * a doctors row, and an "assistant" when users.doctor_id is set. The Doctor the
 * user operates as is stashed on the request for controllers to reuse.
 *
 * Usage: ->middleware('clinic.role:doctor'), 'clinic.role:assistant', or
 * 'clinic.role:doctor,assistant' for shared areas.
 */
class EnsureClinicRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Deactivated mid-session (the doctor's own account, or the doctor an
        // assistant works for): end the session rather than serve the workspace.
        if ($user->isSuspended()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => app()->getLocale() === 'ar'
                    ? 'حسابك غير مفعّل. يرجى التواصل مع الإدارة لتفعيل الحساب.'
                    : 'Your account is not active. Please contact the administration to activate it.',
            ]);
        }

        $roles = $roles ?: ['doctor', 'assistant'];

        $isDoctor = in_array('doctor', $roles, true) && $user->isDoctor();
        $isAssistant = in_array('assistant', $roles, true) && $user->isAssistant();

        if (! $isDoctor && ! $isAssistant) {
            abort(403, app()->getLocale() === 'ar'
                ? 'هذا القسم خاص بالعيادة (طبيب / مساعد الطبيب).'
                : 'This area is for clinic accounts (doctor / doctor assistant).');
        }

        $doctor = $user->clinicDoctor();
        if (! $doctor) {
            abort(403, 'No doctor profile is linked to this account.');
        }

        $request->attributes->set('clinic_doctor', $doctor);

        return $next($request);
    }
}
