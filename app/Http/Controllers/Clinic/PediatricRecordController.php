<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PediatricVaccination;
use App\Models\PediatricVisitRecord;
use App\Support\PediatricCare;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The paediatric growth + vaccination record on the examination screen.
 *
 * Shown only for paediatric / newborn clinics (fixed by the acting doctor's
 * specialisation, never taken from the request). Measurements are kept one row
 * per visit — edited in place — while vaccinations accumulate as a log.
 */
class PediatricRecordController extends Controller
{
    use ClinicContext;

    public function storeMeasurement(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->guard($request, $appointment);

        $data = $request->validate([
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:250'],
            'head_circumference_cm' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'temperature_c' => ['nullable', 'numeric', 'min:20', 'max:45'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // Don't store an empty row: at least one field must carry a value.
        $hasValue = collect($data)->contains(fn ($v) => $v !== null && $v !== '');
        if (! $hasValue) {
            return $this->back($appointment, __('app.pediatric.measurement_empty'), 'error');
        }

        PediatricVisitRecord::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'client_id' => $appointment->client_id,
                'doctor_id' => $doctor->id,
                'weight_kg' => $data['weight_kg'] ?? null,
                'height_cm' => $data['height_cm'] ?? null,
                'head_circumference_cm' => $data['head_circumference_cm'] ?? null,
                'temperature_c' => $data['temperature_c'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_at' => now(),
            ],
        );

        return $this->back($appointment, __('app.pediatric.measurement_saved'));
    }

    public function storeVaccination(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->guard($request, $appointment);

        $data = $request->validate([
            'vaccine' => ['required', 'string', 'max:160'],
            'dose' => ['nullable', 'string', 'max:80'],
            'given_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        PediatricVaccination::create([
            'client_id' => $appointment->client_id,
            'clinic_id' => $appointment->clinic_id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'vaccine' => $data['vaccine'],
            'dose' => $data['dose'] ?? null,
            'given_at' => $data['given_at'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $this->back($appointment, __('app.pediatric.vaccination_added'));
    }

    /**
     * Remove a dose. A doctor may only take back one they recorded themselves —
     * another doctor's record of this child is not theirs to erase.
     */
    public function destroyVaccination(Request $request, Appointment $appointment, PediatricVaccination $vaccination): RedirectResponse
    {
        $doctor = $this->guard($request, $appointment);
        abort_unless((int) $vaccination->client_id === (int) $appointment->client_id, 404);
        abort_unless((int) $vaccination->doctor_id === (int) $doctor->id, 403);

        $vaccination->delete();

        return $this->back($appointment, __('app.pediatric.vaccination_removed'));
    }

    /**
     * The acting doctor, having confirmed this is their appointment and their
     * clinic is a paediatric one. The record exists for no other specialisation.
     */
    private function guard(Request $request, Appointment $appointment): Doctor
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $doctor->loadMissing('specialization');
        abort_unless(PediatricCare::forSpecialization($doctor->specialization), 404);

        return $doctor;
    }

    private function back(Appointment $appointment, string $status, string $tone = 'status'): RedirectResponse
    {
        return redirect()
            ->to(route('practice.doctor.examine', $appointment).'#pediatric')
            ->with($tone, $status);
    }
}
