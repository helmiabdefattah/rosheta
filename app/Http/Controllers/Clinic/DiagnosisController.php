<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    use ClinicContext;

    /** Save (or update) the diagnosis and treatment plan for the appointment. */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $data = $request->validate([
            'diagnosis' => ['required', 'string'],
            'treatment_plan' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $appointment->diagnosis()->updateOrCreate(
            [],
            [
                'client_id' => $appointment->client_id,
                'doctor_id' => $doctor->id,
                'diagnosis' => $data['diagnosis'],
                'treatment_plan' => $data['treatment_plan'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return back()->with('status', __('app.examine.diagnosis_saved'));
    }
}
