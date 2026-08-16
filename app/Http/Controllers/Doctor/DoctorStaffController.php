<?php

namespace App\Http\Controllers\Doctor;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The doctor's own staff page: create and manage the assistant logins for each
 * of their clinics. Each clinic takes up to `doctors.assistant_limit`
 * assistants (2 by default) — an admin raises that number per doctor.
 */
class DoctorStaffController extends DoctorDashboardController
{
    public function index(Request $request)
    {
        $doctor = $this->doctor($request);
        $clinics = $this->ownedClinics($doctor);
        $assistants = $doctor->assistants()->orderBy('name')->get();

        return view('doctor.staff.index', [
            'doctor' => $doctor,
            'clinics' => $clinics,
            'limit' => $doctor->assistantLimit(),
            'assistantsByClinic' => $assistants->groupBy('clinic_id'),
            'unassigned' => $assistants->whereNull('clinic_id'),
        ]);
    }

    public function store(Request $request)
    {
        $doctor = $this->doctor($request);
        $validated = $request->validate([
            'clinic_id' => ['required', Rule::in($this->ownedClinics($doctor)->pluck('id')->all())],
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'phone_number' => 'required|string|max:50|unique:users,phone_number',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $clinicId = (int) $validated['clinic_id'];
        if (! $doctor->canAddAssistantAt($clinicId)) {
            return back()->with('error', $this->limitReachedMessage($doctor))->withInput();
        }

        User::create([
            'name' => $validated['name'],
            // The email column is unique, so a blank one has to be stored as
            // NULL — several assistants can be phone-only.
            'email' => filled($validated['email'] ?? null) ? $validated['email'] : null,
            'phone_number' => $validated['phone_number'],
            'password' => $validated['password'],
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinicId,
            'is_active' => true,
        ]);

        return redirect()->route('doctor.staff.index')->with(
            'success',
            app()->getLocale() === 'ar' ? 'تم إضافة المساعد بنجاح' : 'Assistant added successfully.'
        );
    }

    public function update(Request $request, User $assistant)
    {
        $doctor = $this->doctor($request);
        $this->authorizeAssistant($doctor, $assistant);

        $validated = $request->validate([
            'clinic_id' => ['required', Rule::in($this->ownedClinics($doctor)->pluck('id')->all())],
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($assistant->id)],
            'phone_number' => ['required', 'string', 'max:50', Rule::unique('users', 'phone_number')->ignore($assistant->id)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Moving an assistant to another clinic has to respect that clinic's cap.
        $clinicId = (int) $validated['clinic_id'];
        if ($clinicId !== (int) $assistant->clinic_id
            && $doctor->assistantsAtClinic($clinicId)->count() >= $doctor->assistantLimit()) {
            return back()->with('error', $this->limitReachedMessage($doctor))->withInput();
        }

        $payload = [
            'name' => $validated['name'],
            // Cleared email → NULL, so the unique index stays happy.
            'email' => filled($validated['email'] ?? null) ? $validated['email'] : null,
            'phone_number' => $validated['phone_number'],
            'clinic_id' => $clinicId,
        ];
        if (filled($validated['password'] ?? null)) {
            $payload['password'] = $validated['password'];
        }
        $assistant->update($payload);

        return redirect()->route('doctor.staff.index')->with(
            'success',
            app()->getLocale() === 'ar' ? 'تم تحديث بيانات المساعد' : 'Assistant updated.'
        );
    }

    public function toggleActive(Request $request, User $assistant)
    {
        $doctor = $this->doctor($request);
        $this->authorizeAssistant($doctor, $assistant);

        $assistant->update(['is_active' => ! $assistant->is_active]);
        $ar = app()->getLocale() === 'ar';

        return back()->with('success', $assistant->is_active
            ? ($ar ? 'تم تفعيل حساب المساعد' : 'Assistant account activated.')
            : ($ar ? 'تم إيقاف حساب المساعد' : 'Assistant account deactivated.'));
    }

    public function destroy(Request $request, User $assistant)
    {
        $doctor = $this->doctor($request);
        $this->authorizeAssistant($doctor, $assistant);

        $assistant->delete();

        return redirect()->route('doctor.staff.index')->with(
            'success',
            app()->getLocale() === 'ar' ? 'تم حذف المساعد' : 'Assistant removed.'
        );
    }

    /**
     * Clinics this doctor owns — the ones they may staff. Clinics they merely
     * visit (linked through the pivot) belong to another doctor's front desk.
     */
    protected function ownedClinics(Doctor $doctor)
    {
        return Clinic::where('doctor_id', $doctor->id)->orderBy('name')->get();
    }

    protected function authorizeAssistant(Doctor $doctor, User $assistant): void
    {
        abort_unless((int) $assistant->doctor_id === (int) $doctor->id, 403);
    }

    protected function limitReachedMessage(Doctor $doctor): string
    {
        $limit = $doctor->assistantLimit();

        return app()->getLocale() === 'ar'
            ? "لقد وصلت إلى الحد الأقصى ({$limit}) من المساعدين لهذه العيادة. تواصل مع الإدارة لرفع الحد."
            : "You have reached the limit of {$limit} assistants for this clinic. Contact the administration to raise it.";
    }
}
