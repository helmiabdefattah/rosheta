<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    use ClinicContext;

    /** Create a prescription (medicines, doses, durations) for the appointment. */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $data = $request->validate([
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['required', 'string', 'max:255'],
            'items.*.dose' => ['nullable', 'string', 'max:255'],
            'items.*.frequency' => ['nullable', 'string', 'max:255'],
            'items.*.duration' => ['nullable', 'string', 'max:255'],
            'items.*.instructions' => ['nullable', 'string', 'max:255'],
        ]);

        $prescription = $appointment->prescriptions()->create([
            'code' => 'RX-'.strtoupper(Str::random(8)),
            'client_id' => $appointment->client_id,
            'doctor_id' => $doctor->id,
            'diagnosis_id' => $appointment->diagnosis?->id,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            if (blank($item['medicine_name'] ?? null)) {
                continue;
            }
            $prescription->items()->create($item);
        }

        return back()->with('status', __('app.examine.prescription_created', ['code' => $prescription->code]));
    }

    /** Print-friendly view of a prescription. */
    public function print(Request $request, Prescription $prescription): View
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($prescription->doctor_id === $doctor->id, 403);

        $prescription->load(['items', 'client', 'doctor', 'diagnosis', 'appointment.clinic']);

        return view('clinic.prescriptions.print', compact('prescription'));
    }
}
