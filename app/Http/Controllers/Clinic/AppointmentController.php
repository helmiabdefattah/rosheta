<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    use ClinicContext;

    /**
     * Create a new appointment. The assistant may pick an existing patient
     * (client) or register a new one inline by providing a name.
     */
    public function store(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);

        $data = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'new_patient_name' => ['nullable', 'string', 'max:255'],
            'new_patient_phone' => ['nullable', 'string', 'max:50'],
            'new_patient_gender' => ['nullable', 'in:male,female'],
            'type' => ['required', 'in:examination,consultation'],
            'scheduled_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! empty($data['client_id'])) {
            $clientId = $data['client_id'];
        } elseif (! empty($data['new_patient_name'])) {
            $clientId = Client::create([
                'name' => $data['new_patient_name'],
                'phone_number' => $data['new_patient_phone'] ?? '',
                'gender' => $data['new_patient_gender'] ?? null,
            ])->id;
        } else {
            return back()
                ->withErrors(['client_id' => __('app.appointment.patient_required')])
                ->withInput();
        }

        $scheduledAt = Carbon::parse($data['scheduled_at']);

        Appointment::create([
            'client_id' => $clientId,
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'type' => $data['type'],
            'status' => 'scheduled',
            'source' => 'system',
            'scheduled_at' => $scheduledAt,
            'queue_number' => $this->nextQueueNumber($doctor->id, $scheduledAt),
            'price' => $clinic->priceFor($data['type']),
            'reason' => $data['reason'] ?? null,
        ]);

        return back()->with('status', __('app.appointment.created'));
    }

    /**
     * Move an appointment through its lifecycle.
     * Assistants: start / complete / cancel. Doctors also use "start".
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($request, $appointment);

        $data = $request->validate([
            'status' => ['required', 'in:scheduled,under_examination,completed,cancelled,escaped'],
        ]);

        // Only one patient per doctor can be "under examination" at a time.
        // Starting a new examination completes whoever was already running,
        // regardless of day, so a stale running patient is always closed out.
        if ($data['status'] === 'under_examination') {
            Appointment::where('doctor_id', $appointment->doctor_id)
                ->where('status', 'under_examination')
                ->where('id', '!=', $appointment->id)
                ->update(['status' => 'completed']);
        }

        $appointment->update(['status' => $data['status']]);

        // When an examination starts, hand the patient + sort number to the
        // next page so it can announce the patient by voice (see layout). We
        // announce the sort position shown on screen, not the raw queue number
        // (which may be empty), so the voice matches the display and the table.
        if ($data['status'] === 'under_examination') {
            $request->session()->flash('announce', [
                'patient_number' => $appointment->client_id,
                'queue_number' => $this->sortNumber($appointment),
            ]);
        }

        return back()->with('status', __('app.appointment.status_changed', ['status' => $appointment->statusLabel()]));
    }

    /** Small printable queue receipt/ticket for the patient. */
    public function ticket(Request $request, Appointment $appointment): View
    {
        $this->authorizeAppointment($request, $appointment);
        $appointment->load(['client', 'doctor', 'clinic']);

        return view('clinic.appointments.ticket', compact('appointment'));
    }

    /**
     * 1-based position of an appointment among the doctor's appointments on its
     * day, ordered the same way the dashboards list them (queue number, then
     * time). This is the number shown on screen and announced by voice.
     */
    protected function sortNumber(Appointment $appointment): int
    {
        $position = Appointment::where('doctor_id', $appointment->doctor_id)
            ->whereDate('scheduled_at', $appointment->scheduled_at->toDateString())
            ->orderBy('queue_number')
            ->orderBy('scheduled_at')
            ->pluck('id')
            ->search($appointment->id);

        return $position === false ? (int) $appointment->queue_number : $position + 1;
    }

    /** Next queue number for a doctor on the appointment's day. */
    protected function nextQueueNumber(int $doctorId, Carbon $day): int
    {
        return (Appointment::where('doctor_id', $doctorId)
            ->whereDate('scheduled_at', $day->toDateString())
            ->max('queue_number') ?? 0) + 1;
    }

    protected function authorizeAppointment(Request $request, Appointment $appointment): void
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);
    }
}
