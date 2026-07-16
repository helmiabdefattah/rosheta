<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicController extends Controller
{
    use ClinicContext;

    /**
     * Switch the workspace to another of the doctor's clinics. The id is
     * resolved through their own clinics, so a foreign id 404s rather than
     * being stored.
     */
    public function switchClinic(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        $id = $request->validate(['clinic_id' => ['required', 'integer']])['clinic_id'];

        $clinic = $doctor->clinics()->whereKey($id)->firstOrFail();
        session([Doctor::ACTIVE_CLINIC_SESSION_KEY => $clinic->id]);

        return back()->with('status', __('app.clinic.switched', ['name' => $clinic->name]));
    }

    /** Show the clinic scheduling settings (opening hours + appointment duration). */
    public function edit(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);

        return view('clinic.doctor.clinic', compact('clinic'));
    }

    /** Persist opening hours per day and the appointment duration. */
    public function update(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);

        $data = $request->validate([
            'appointment_duration' => ['required', 'integer', 'min:5', 'max:240'],
            'days' => ['required', 'array'],
            'days.*.open' => ['nullable', 'date_format:H:i'],
            'days.*.close' => ['nullable', 'date_format:H:i', 'after:days.*.open'],
        ]);

        $hours = [];
        foreach (Clinic::DAYS as $day) {
            $row = $data['days'][$day] ?? [];
            $closed = ! $request->boolean("days.$day.enabled");

            $hours[$day] = [
                'open' => $closed ? null : ($row['open'] ?? '09:00'),
                'close' => $closed ? null : ($row['close'] ?? '17:00'),
                'closed' => $closed,
            ];
        }

        $clinic->update([
            'opening_hours' => $hours,
            'appointment_duration' => $data['appointment_duration'],
        ]);

        // Mirror the schedule into the rosheta workingHours table so the other
        // clinic-edit page (and patient booking) stays in sync.
        $clinic->syncWorkingHoursFromOpeningHours();

        return back()->with('status', __('app.clinic.saved'));
    }
}
