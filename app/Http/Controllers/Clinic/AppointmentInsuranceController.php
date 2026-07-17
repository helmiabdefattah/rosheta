<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\InsuranceCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Assign (or update) the medical-insurance split for an appointment: pick an
 * existing insurance company or add a new one, and set how much the patient
 * pays vs. how much is claimed from the company.
 */
class AppointmentInsuranceController extends Controller
{
    use ClinicContext;

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($request, $appointment);

        $data = $request->validate([
            'insurance_company_id' => ['nullable', 'exists:insurance_companies,id'],
            'new_company_name' => ['nullable', 'string', 'max:255'],
            'patient_amount' => ['required', 'numeric', 'min:0', 'max:999999'],
            'insurance_amount' => ['required', 'numeric', 'min:0', 'max:999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if (blank($data['insurance_company_id']) && blank($data['new_company_name'])) {
            return back()->withErrors([
                'insurance_company_id' => __('app.insurance.company_required'),
            ]);
        }

        // Add a new company on the fly, reusing one with the same name if present.
        if (filled($data['new_company_name'])) {
            $company = InsuranceCompany::firstOrCreate(
                ['name' => trim($data['new_company_name'])],
                ['is_active' => true],
            );
            $companyId = $company->id;
        } else {
            $companyId = $data['insurance_company_id'];
        }

        $appointment->insurance()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'insurance_company_id' => $companyId,
                'patient_amount' => $data['patient_amount'],
                'insurance_amount' => $data['insurance_amount'],
                'note' => $data['note'] ?? null,
                'created_by' => $request->user()?->id,
            ],
        );

        return back()->with('status', __('app.insurance.saved'));
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($request, $appointment);
        $appointment->insurance()->delete();

        return back()->with('status', __('app.insurance.removed'));
    }

    protected function authorizeAppointment(Request $request, Appointment $appointment): void
    {
        abort_unless($appointment->doctor_id === $this->clinicDoctor($request)->id, 403);
    }
}
