<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MedicalRequestController extends Controller
{
    use ClinicContext;

    /** Order a medical examination, lab test or radiology for the patient. */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $data = $request->validate([
            'type' => ['required', 'in:examination,lab_test,radiology'],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $appointment->medicalRequests()->create([
            'client_id' => $appointment->client_id,
            'doctor_id' => $doctor->id,
            'type' => $data['type'],
            'name' => $data['name'],
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', __('app.examine.request_added'));
    }

    public function destroy(Request $request, MedicalRequest $medicalRequest): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($medicalRequest->doctor_id === $doctor->id, 403);

        $medicalRequest->delete();

        return back()->with('status', __('app.examine.request_removed'));
    }
}
