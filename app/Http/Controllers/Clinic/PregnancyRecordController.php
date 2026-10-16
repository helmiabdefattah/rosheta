<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PregnancyRecord;
use App\Models\PregnancyVisit;
use App\Support\WomensCare;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The pregnancy follow-up on the examination screen for women's clinics.
 *
 * Shown only for women's / obstetric specialisations (fixed by the acting
 * doctor's specialisation, never taken from the request). The dating is one row
 * per patient, edited in place; the fetal/maternal data is one row per visit.
 */
class PregnancyRecordController extends Controller
{
    use ClinicContext;

    /** Set or correct the pregnancy dating (and the mother's summary). */
    public function storeRecord(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->guard($request, $appointment);

        $data = $request->validate([
            'dating_method' => ['required', Rule::in(['lmp', 'manual'])],
            'lmp_date' => ['nullable', 'date', 'before_or_equal:today', 'required_if:dating_method,lmp'],
            'ga_assessed_on' => ['nullable', 'date', 'before_or_equal:today', 'required_if:dating_method,manual'],
            'ga_weeks' => ['nullable', 'integer', 'min:0', 'max:45', 'required_if:dating_method,manual'],
            'ga_days' => ['nullable', 'integer', 'min:0', 'max:6'],
            'gravida' => ['nullable', 'integer', 'min:0', 'max:30'],
            'para' => ['nullable', 'integer', 'min:0', 'max:30'],
            'blood_group' => ['nullable', 'string', 'max:8'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $manual = $data['dating_method'] === 'manual';

        PregnancyRecord::updateOrCreate(
            ['client_id' => $appointment->client_id],
            [
                'doctor_id' => $doctor->id,
                'dating_method' => $data['dating_method'],
                // Keep only the fields the chosen method uses, so the other
                // method's stale values can never drive the calculation.
                'lmp_date' => $manual ? null : $data['lmp_date'],
                'ga_assessed_on' => $manual ? $data['ga_assessed_on'] : null,
                'ga_weeks' => $manual ? $data['ga_weeks'] : null,
                'ga_days' => $manual ? ($data['ga_days'] ?? 0) : null,
                'gravida' => $data['gravida'] ?? null,
                'para' => $data['para'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_active' => true,
            ],
        );

        return $this->back($appointment, __('app.pregnancy.dating_saved'));
    }

    /** Record this visit's fetal + maternal data (edited in place per visit). */
    public function storeVisit(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->guard($request, $appointment);

        $data = $request->validate([
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'blood_pressure' => ['nullable', 'string', 'max:12'],
            'fundal_height_cm' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'fetal_heart_rate' => ['nullable', 'integer', 'min:0', 'max:300'],
            'presentation' => ['nullable', 'string', 'max:40'],
            'fetal_weight_g' => ['nullable', 'integer', 'min:0', 'max:9000'],
            'fetus_count' => ['nullable', 'integer', 'min:1', 'max:6'],
            'ultrasound' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $hasValue = collect($data)->contains(fn ($v) => $v !== null && $v !== '');
        if (! $hasValue) {
            return $this->back($appointment, __('app.pregnancy.visit_empty'), 'error');
        }

        // Freeze the gestational age as it stands on the visit date.
        $record = PregnancyRecord::where('client_id', $appointment->client_id)->first();
        $visitDate = $appointment->scheduled_at ?? now();

        PregnancyVisit::updateOrCreate(
            ['appointment_id' => $appointment->id],
            array_merge($data, [
                'client_id' => $appointment->client_id,
                'doctor_id' => $doctor->id,
                'ga_days' => $record?->gestationalAgeDays($visitDate),
                'recorded_at' => now(),
            ]),
        );

        return $this->back($appointment, __('app.pregnancy.visit_saved'));
    }

    /**
     * The acting doctor, having confirmed this is their appointment and their
     * clinic is a women's one.
     */
    private function guard(Request $request, Appointment $appointment): Doctor
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $doctor->loadMissing('specialization');
        abort_unless(WomensCare::forSpecialization($doctor->specialization), 404);

        return $doctor;
    }

    private function back(Appointment $appointment, string $status, string $tone = 'status'): RedirectResponse
    {
        return redirect()
            ->to(route('practice.doctor.examine', $appointment).'#pregnancy')
            ->with($tone, $status);
    }
}
