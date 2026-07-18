<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    use ClinicContext;

    public function show(Request $request, Client $patient): View
    {
        $doctor = $this->clinicDoctor($request);

        $patient->load([
            'attachments.uploader',
            'prescriptions' => fn ($q) => $q->where('doctor_id', $doctor->id)->with('items'),
            // Full examination history — every doctor's records, newest first.
            'diagnoses' => fn ($q) => $q->with(['doctor', 'appointment'])->latest(),
        ]);

        // Only this doctor's visits with the patient.
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->where('client_id', $patient->id)
            ->with(['clinic', 'items', 'collections.collector'])
            ->orderByDesc('scheduled_at')
            ->get();

        // Money across every visit, so the front desk can settle what was never
        // collected on the day. Cancelled visits are excluded — nothing is owed.
        $billable = $appointments->where('status', '!=', 'cancelled');
        $totals = [
            'due' => round($billable->sum(fn (Appointment $a) => $a->dueAmount()), 2),
            'collected' => round($billable->sum(fn (Appointment $a) => $a->collectedAmount()), 2),
            'outstanding' => round($billable->sum(fn (Appointment $a) => $a->remainingAmount()), 2),
        ];

        // Lab / radiology results from the labs system (client/test-results data).
        $labResults = \App\Models\Offer::labResultsForClient($patient->id);

        return view('clinic.patients.show', compact('patient', 'appointments', 'doctor', 'totals', 'labResults'));
    }

    /** Update a patient's file (profile details). */
    public function update(Request $request, Client $patient): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'dob' => ['nullable', 'date'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:500'],
            'allergies' => ['nullable', 'string'],
            'chronic_diseases' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'string'],
        ]);

        foreach (['allergies', 'chronic_diseases'] as $listField) {
            if ($request->has($listField)) {
                $data[$listField] = Client::toList($data[$listField] ?? null);
            } else {
                unset($data[$listField]);
            }
        }

        $patient->update($data);

        return back()->with('status', __('app.patient.updated'));
    }

    /** Update only the allergies list (doctor, examination screen). */
    public function updateAllergies(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($request, $appointment);

        $data = $request->validate([
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['nullable', 'string', 'max:255'],
        ]);

        $appointment->client->update([
            'allergies' => Client::toList($data['allergies'] ?? []),
        ]);

        return back()->with('status', __('app.examine.allergies_updated'));
    }

    /** Update only the chronic diseases list (doctor, examination screen). */
    public function updateChronicDiseases(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($request, $appointment);

        $data = $request->validate([
            'chronic_diseases' => ['nullable', 'array'],
            'chronic_diseases.*' => ['nullable', 'string', 'max:255'],
        ]);

        $appointment->client->update([
            'chronic_diseases' => Client::toList($data['chronic_diseases'] ?? []),
        ]);

        return back()->with('status', __('app.examine.chronic_updated'));
    }

    protected function authorizeAppointment(Request $request, Appointment $appointment): void
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);
    }
}
