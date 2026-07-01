<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\BuildsClinicCalendar;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DoctorDashboardController extends Controller
{
    use BuildsClinicCalendar, ClinicContext;

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);

        $selectedDate = Carbon::hasFormat((string) $request->query('date'), 'Y-m-d')
            ? Carbon::createFromFormat('Y-m-d', $request->query('date'))->startOfDay()
            : Carbon::today();

        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', $selectedDate)
            ->with(['client', 'prescriptions'])
            ->orderBy('queue_number')
            ->orderBy('scheduled_at')
            ->get();

        $current = $selectedDate->isToday()
            ? $appointments->firstWhere('status', 'under_examination')
            : null;

        return view('clinic.doctor.dashboard', [
            'doctor' => $doctor,
            'clinic' => $clinic,
            'appointments' => $appointments,
            'current' => $current,
            'selectedDate' => $selectedDate,
            'waiting' => $appointments->where('status', 'scheduled')->values(),
            'calendar' => $this->buildCalendar($request, $doctor, $clinic, $selectedDate),
        ]);
    }

    /** The examination screen for a single patient. */
    public function examine(Request $request, Appointment $appointment): View
    {
        $this->authorizeAppointment($request, $appointment);

        $appointment->load([
            'client.attachments',
            'diagnosis',
            'prescriptions.items',
            'medicalRequests',
        ]);

        return view('clinic.doctor.examine', compact('appointment'));
    }

    /** Guard: an appointment must belong to the acting doctor. */
    protected function authorizeAppointment(Request $request, Appointment $appointment): void
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);
    }
}
