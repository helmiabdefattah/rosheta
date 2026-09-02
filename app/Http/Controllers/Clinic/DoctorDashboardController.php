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
            // The patient's examination history across all their visits & doctors.
            // Each past visit's prescriptions ride along with its diagnosis so the
            // history can show what was prescribed that day.
            'client.diagnoses' => fn ($q) => $q->with(['doctor', 'appointment.prescriptions.items'])->latest(),
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

        // Autocomplete options for the "Examinations, Lab Tests & Radiology"
        // form, grouped by request type. Names come from the medical-tests
        // catalogue (localised); the doctor may still type a value not listed.
        $testSuggestions = $this->testSuggestions();

        // Used by the view to decide which history entries this doctor may edit.
        $actingDoctorId = $doctor->id;

        // The patient's lab / radiology results from the labs system — the same
        // data shown to the patient at client/test-results. Read-only here.
        $labResults = \App\Models\Offer::labResultsForClient($appointment->client_id);

        // The clinical chart under the diagnosis is chosen by the acting
        // doctor's specialisation, so the view needs both.
        $doctor->loadMissing('specialization');

        // Who is next in today's queue at this clinic, so the doctor can call
        // them from here without going back to the dashboard. Ordered exactly
        // as the dashboards list the queue.
        $nextPatient = Appointment::where('doctor_id', $doctor->id)
            ->where('clinic_id', $appointment->clinic_id)
            ->whereDate('scheduled_at', today())
            ->where('status', 'scheduled')
            ->where('id', '!=', $appointment->id)
            ->with('client')
            ->orderBy('queue_number')
            ->orderBy('scheduled_at')
            ->first();

        return view('clinic.doctor.examine', compact(
            'appointment', 'doctor', 'nextPatient', 'billableItems', 'medicalPlans', 'examinationFields',
            'examinationValues', 'testSuggestions', 'actingDoctorId', 'labResults'
        ));
    }

    /**
     * Localised name suggestions per medical-request type, drawn from the
     * shared medical-tests catalogue. 'examination' has no catalogue, so it
     * stays free-text only.
     */
    protected function testSuggestions(): array
    {
        $ar = app()->getLocale() === 'ar';
        $name = fn ($t) => $ar
            ? ($t->test_name_ar ?: $t->test_name_en)
            : ($t->test_name_en ?: $t->test_name_ar);

        $forType = fn (string $catalogueType) => \App\Models\MedicalTest::where('type', $catalogueType)
            ->get(['test_name_en', 'test_name_ar'])
            ->map($name)
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return [
            'lab_test' => $forType('test'),
            'radiology' => $forType('radiology'),
        ];
    }

    /** Guard: an appointment must belong to the acting doctor. */
    protected function authorizeAppointment(Request $request, Appointment $appointment): void
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);
    }
}
