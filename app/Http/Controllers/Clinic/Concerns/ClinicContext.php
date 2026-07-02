<?php

namespace App\Http\Controllers\Clinic\Concerns;

use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\Request;

/**
 * Resolves the Doctor + Clinic the current clinic-system request operates on.
 * The doctor comes from the auth user (their own profile, or the doctor they
 * assist); the active clinic is that doctor's first clinic.
 */
trait ClinicContext
{
    protected function clinicDoctor(Request $request): Doctor
    {
        return $request->attributes->get('clinic_doctor')
            ?? $request->user()->clinicDoctor();
    }

    /** The doctor's primary clinic, creating a bare one if none exists yet. */
    protected function activeClinic(Doctor $doctor): Clinic
    {
        return $doctor->clinics()->first()
            ?? $doctor->clinics()->create([
                'name' => $doctor->name,
                'appointment_duration' => 30,
            ]);
    }
}
