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

        // Scoped to the clinic the doctor is currently switched into, so a
        // multi-clinic doctor sees one clinic's queue at a time.
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->where('clinic_id', $clinic->id)
            ->whereDate('scheduled_at', $selectedDate)
            ->with(['client', 'prescriptions', 'clinic', 'items', 'collections', 'insurance.insuranceCompany'])
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
            'calendar' => $this->buildCalendar($request, $doctor, $clinic, $selectedDate, scopeToClinic: true),
            'clinics' => $doctor->clinics()->orderBy('name')->get(),
        ]);
    }

    /** The examination screen for a single patient. */
    public function examine(Request $request, Appointment $appointment): View
    {
        $this->authorizeAppointment($request, $appointment);

        $appointment->load([
            'client.attachments',
            'client.patientTests' => fn ($q) => $q->with('attachments')->latest(),
            'diagnosis',
            'prescriptions.items',
            'medicalRequests',
            'clinic',
            'items',
            'collections',
            'insurance.insuranceCompany',
            'examinationValues.attachment',
        ]);

        $doctor = $this->clinicDoctor($request);

        // The doctor's price list, shared across their clinics.
        $billableItems = $doctor->billableItems()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Reusable prescription templates + custom examination fields.
        $medicalPlans = $doctor->medicalPlans()->with('items')->orderBy('title')->get();
        $examinationFields = $doctor->examinationFields()
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')
            ->get();
        $examinationValues = $appointment->examinationValues->keyBy('examination_field_id');

        return view('clinic.doctor.examine', compact(
            'appointment', 'billableItems', 'medicalPlans', 'examinationFields', 'examinationValues'
        ));
    }

    /** Guard: an appointment must belong to the acting doctor. */
    protected function authorizeAppointment(Request $request, Appointment $appointment): void
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);
    }
}
