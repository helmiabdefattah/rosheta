<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use App\Models\Specialization;
use App\Services\ClinicOnboardingService;
use Illuminate\Http\Request;

/**
 * "Set up a clinic in one go": create a doctor with a login, their clinic, a
 * doctor's assistant and — optionally — a full demo dataset (patients,
 * appointments for today and the next few days, diagnoses with treatment
 * roadmaps, prescriptions, lab / radiology tests, payments and insurance) so
 * the clinic workspace can be presented immediately after creation.
 */
class ClinicOnboardingController extends Controller
{
    public function __construct(private readonly ClinicOnboardingService $onboarding)
    {
    }

    public function create()
    {
        $specializations = Specialization::orderBy('name')->get();
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();

        return view('admin.clinic-onboarding.create', compact('specializations', 'governorates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Doctor
            'doctor_name' => 'required|string|max:255',
            'specialization_id' => 'required|exists:specializations,id',
            'brief' => 'nullable|string|max:5000',
            'doctor_email' => 'nullable|email|max:255|unique:users,email',
            'doctor_phone' => 'nullable|string|max:50|unique:users,phone_number',
            'doctor_password' => 'nullable|string|min:8',

            // Clinic — "if no clinic name is given, use the doctor's name"
            'clinic_name' => 'nullable|string|max:255',
            'address' => 'required|string|max:1000',
            'phone_number' => 'nullable|string|max:50',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'area_id' => 'nullable|exists:areas,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'medical_examination_price' => 'required|numeric|min:0',
            'follow_up_price' => 'required|numeric|min:0',
            'appointment_duration' => 'required|integer|min:5|max:180',
            'open_from' => 'required|date_format:H:i',
            'open_to' => 'required|date_format:H:i|after:open_from',
            'closed_days' => 'nullable|array',
            'closed_days.*' => 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',

            // Assistant
            'assistant_name' => 'nullable|string|max:255',
            'assistant_email' => 'nullable|email|max:255|unique:users,email|different:doctor_email',
            'assistant_phone' => 'nullable|string|max:50|unique:users,phone_number|different:doctor_phone',
            'assistant_password' => 'nullable|string|min:8',

            // Demo data
            'seed_demo' => 'nullable|boolean',
            'patients_count' => 'required_if:seed_demo,1|nullable|integer|min:1|max:12',
            'appointments_per_day' => 'required_if:seed_demo,1|nullable|integer|min:1|max:20',
            'days_ahead' => 'required_if:seed_demo,1|nullable|integer|min:0|max:14',
            'seed_history' => 'nullable|boolean',
            'history_visits' => 'nullable|integer|min:0|max:20',
        ]);

        $validated['seed_demo'] = $request->boolean('seed_demo');
        $validated['seed_history'] = $request->boolean('seed_history');
        $validated['closed_days'] = $validated['closed_days'] ?? [];

        // Blank optional inputs mean "generate one for me", not "store empty".
        foreach (['doctor_email', 'doctor_phone', 'doctor_password', 'assistant_email', 'assistant_phone', 'assistant_password', 'clinic_name', 'assistant_name'] as $key) {
            if (blank($validated[$key] ?? null)) {
                unset($validated[$key]);
            }
        }

        $result = $this->onboarding->onboard($validated);

        // Credentials include the plain passwords, so hand them over once via a
        // flash and let the result page render them.
        return redirect()
            ->route('admin.clinic-onboarding.create')
            ->with('onboarding_result', [
                'doctor_id' => $result['doctor']->id,
                'doctor_name' => $result['doctor']->name,
                'clinic_id' => $result['clinic']->id,
                'clinic_name' => $result['clinic']->name,
                'credentials' => $result['credentials'],
                'stats' => $result['stats'],
            ]);
    }
}
