<?php

namespace App\Http\Middleware;

use App\Support\ClinicalChart;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the Dental Lab module to dental clinics only. Runs after clinic.role, so
 * the acting Doctor is already resolved and stashed on the request; a clinic is
 * "dental" when the doctor's specialisation maps to the teeth chart (the same
 * rule the clinical chart uses).
 *
 * Usage: ->middleware(['clinic.role:doctor,assistant', 'clinic.dental'])
 */
class EnsureDentalClinic
{
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = $request->attributes->get('clinic_doctor')
            ?? $request->user()?->clinicDoctor();

        $isDental = $doctor
            && ClinicalChart::forSpecialization($doctor->specialization) === ClinicalChart::TEETH;

        if (! $isDental) {
            abort(403, app()->getLocale() === 'ar'
                ? 'وحدة معمل الأسنان متاحة لعيادات الأسنان فقط.'
                : 'The Dental Lab module is for dental clinics only.');
        }

        return $next($request);
    }
}
