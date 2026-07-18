<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientTest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientTestController extends Controller
{
    use ClinicContext;

    /**
     * Attach a lab / radiology test result to the patient. A test groups a type
     * with one or more result files, stored via rosheta's polymorphic
     * attachments and hung off the Client so it shows across all their visits.
     */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $data = $request->validate([
            'type' => ['required', 'in:lab,radiology'],
            'title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:10240'], // 10 MB each
        ]);

        $test = $appointment->client->patientTests()->create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'uploaded_by' => $request->user()->id,
            'type' => $data['type'],
            'title' => $data['title'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('test-results', 'public');

            $test->attachments()->create([
                'uploaded_by' => $request->user()->id,
                'appointment_id' => $appointment->id,
                'title' => $file->getClientOriginalName(),
                'file_type' => $data['type'],
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return back()->with('status', __('app.examine.test_added'));
    }

    public function destroy(Request $request, PatientTest $patientTest): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($patientTest->doctor_id === $doctor->id, 403);

        foreach ($patientTest->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }

        $patientTest->delete();

        return back()->with('status', __('app.examine.test_removed'));
    }
}
