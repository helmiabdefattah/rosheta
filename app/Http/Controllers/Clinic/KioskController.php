<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Clinic;
use App\Notifications\PrintQueueTicketNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Self-service Kiosk for a single clinic (public, no login). A patient
 * identifies themselves by phone; the kiosk finds their file, prints today's
 * ticket if they already have an appointment, books a new one, or registers a
 * brand-new patient. Scoped to a clinic via the route parameter.
 */
class KioskController extends Controller
{
    public function welcome(Clinic $clinic): View
    {
        return view('clinic.kiosk.welcome', compact('clinic'));
    }

    public function lookup(Request $request, Clinic $clinic): RedirectResponse|View
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:50']]);
        $phone = trim($data['phone']);
        $patient = $this->findLocalPatient($phone);

        if (! $patient) {
            return redirect()->route('practice.kiosk.register', ['clinic' => $clinic->id, 'phone' => $phone]);
        }

        if ($appointment = $this->todaysAppointment($clinic, $patient)) {
            return redirect()->route('practice.kiosk.ticket', ['clinic' => $clinic->id, 'appointment' => $appointment->id]);
        }

        return view('clinic.kiosk.found', compact('patient', 'clinic'));
    }

    public function register(Request $request, Clinic $clinic): View
    {
        $phone = (string) $request->query('phone', '');

        return view('clinic.kiosk.register', compact('phone', 'clinic'));
    }

    public function store(Request $request, Clinic $clinic): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'gender' => ['nullable', 'in:male,female'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'type' => ['required', 'in:examination,consultation'],
        ]);

        // Patients give their age at the kiosk; store it as a 1 Jan birth date
        // of the implied year so the file still carries a usable date of birth.
        $dob = $request->filled('age')
            ? Carbon::create(Carbon::now()->year - (int) $data['age'], 1, 1)->toDateString()
            : null;

        $patient = Client::create([
            'name' => $data['name'],
            'phone_number' => $data['phone'],
            'gender' => $data['gender'] ?? null,
            'dob' => $dob,
            // Walk-in patients aren't app users; give them a random password so
            // the non-nullable clients.password column is satisfied.
            'password' => Str::random(40),
        ]);

        $appointment = $this->bookAppointment($clinic, $patient, $data['type']);

        return $this->afterCheckIn($clinic, $appointment);
    }

    public function book(Request $request, Clinic $clinic): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'type' => ['required', 'in:examination,consultation'],
        ]);

        $patient = Client::findOrFail($data['client_id']);

        $appointment = $this->todaysAppointment($clinic, $patient)
            ?? $this->bookAppointment($clinic, $patient, $data['type']);

        return $this->afterCheckIn($clinic, $appointment);
    }

    /**
     * When the clinic has a connected Bluetooth printer, the ticket is printed
     * automatically by the staff app (FCM), so skip the browser print page and
     * return to the kiosk welcome screen. Otherwise fall back to browser print.
     */
    private function afterCheckIn(Clinic $clinic, Appointment $appointment): RedirectResponse
    {
        if ($clinic->hasConnectedPrinter()) {
            // Ticket auto-prints on the clinic's Bluetooth printer (FCM); send
            // the patient straight back to the waiting-room check-in display.
            return redirect()->route('practice.display.screen', [
                'clinic' => $clinic->id,
                'checkin' => 1,
                'started' => 1,
            ]);
        }

        // No printer online: fall back to the browser-printable ticket page.
        return redirect()->route('practice.kiosk.ticket', ['clinic' => $clinic->id, 'appointment' => $appointment->id]);
    }

    public function ticket(Clinic $clinic, Appointment $appointment): View
    {
        $appointment->load(['client', 'doctor', 'clinic']);

        return view('clinic.appointments.ticket', compact('appointment'));
    }

    /** Match a local patient by phone, comparing digits only. */
    private function findLocalPatient(string $phone): ?Client
    {
        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === '') {
            return null;
        }

        return Client::whereNotNull('phone_number')
            ->get()
            ->first(fn (Client $c) => preg_replace('/\D/', '', (string) $c->phone_number) === $digits);
    }

    /** Today's still-active appointment for a patient at this clinic, if any. */
    private function todaysAppointment(Clinic $clinic, Client $patient): ?Appointment
    {
        return Appointment::where('clinic_id', $clinic->id)
            ->where('client_id', $patient->id)
            ->whereDate('scheduled_at', today())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('queue_number')
            ->first();
    }

    /** Create a walk-in appointment for today with the next queue number. */
    private function bookAppointment(Clinic $clinic, Client $patient, string $type): Appointment
    {
        $now = Carbon::now();
        $doctorId = $clinic->doctor_id;

        $queue = (Appointment::where('doctor_id', $doctorId)
            ->whereDate('scheduled_at', $now->toDateString())
            ->max('queue_number') ?? 0) + 1;

        $appointment = Appointment::create([
            'client_id' => $patient->id,
            'doctor_id' => $doctorId,
            'clinic_id' => $clinic->id,
            'type' => $type,
            'status' => 'scheduled',
            'source' => 'system',
            'scheduled_at' => $now,
            'queue_number' => $queue,
            'reason' => __('app.kiosk.walk_in'),
        ]);

        // Auto-print the queue ticket on the clinic's Bluetooth printer via
        // the staff mobile app (best effort — check-in must never fail on it).
        try {
            PrintQueueTicketNotification::sendToClinicStaff($appointment);
        } catch (\Throwable $e) {
            report($e);
        }

        return $appointment;
    }
}
