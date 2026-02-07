<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicWorkingHour;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AppointmentController extends Controller
{
    public function index()
    {
        return view('admin.appointments.index');
    }

    public function create()
    {
        $doctors = Doctor::with('specialization')->orderBy('name')->get();
        $clinics = Clinic::with('doctor')->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        return view('admin.appointments.create', compact('doctors', 'clinics', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'user_id' => 'nullable|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'type' => 'required|in:medical_examination,follow_up',
            'notes' => 'nullable|string|max:1000',
        ]);

        $clinic = Clinic::findOrFail($validated['clinic_id']);
        $price = $validated['type'] === 'medical_examination'
            ? $clinic->medical_examination_price
            : $clinic->follow_up_price;

        // Ensure doctor belongs to clinic (primary or via clinic_doctor)
        $clinicDoctorIds = $clinic->doctors()->pluck('doctors.id')->toArray();
        if (empty($clinicDoctorIds)) {
            $clinicDoctorIds = $clinic->doctor_id ? [$clinic->doctor_id] : [];
        }
        if (!in_array((int) $validated['doctor_id'], array_map('intval', $clinicDoctorIds))) {
            return back()->withInput()->withErrors(['doctor_id' => 'Doctor does not belong to the selected clinic.']);
        }

        $exists = Appointment::where('clinic_id', $validated['clinic_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['appointment_time' => 'This slot is already booked.']);
        }

        Appointment::create([
            'doctor_id' => $validated['doctor_id'],
            'clinic_id' => $validated['clinic_id'],
            'user_id' => $validated['user_id'] ?? null,
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'type' => $validated['type'],
            'price' => $price,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.appointments.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حجز الموعد بنجاح' : 'Appointment booked successfully');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['doctor.specialization', 'clinic', 'user']);
        return view('admin.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $appointment->load(['doctor', 'clinic', 'user']);
        $doctors = Doctor::with('specialization')->orderBy('name')->get();
        $clinics = Clinic::with('doctor')->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        return view('admin.appointments.edit', compact('appointment', 'doctors', 'clinics', 'users'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);
        $appointment->update($validated);
        return redirect()->route('admin.appointments.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الموعد' : 'Appointment updated');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('admin.appointments.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الموعد' : 'Appointment deleted');
    }

    public function data()
    {
        $appointments = Appointment::with(['doctor.specialization', 'clinic', 'user'])->select('appointments.*');
        return DataTables::of($appointments)
            ->addColumn('doctor_name', fn ($a) => $a->doctor?->name ?? '-')
            ->addColumn('clinic_name', fn ($a) => $a->clinic?->name ?? '-')
            ->addColumn('patient_name', fn ($a) => $a->user?->name ?? '-')
            ->addColumn('type_label', fn ($a) => $a->type === 'medical_examination'
                ? (app()->getLocale() === 'ar' ? 'كشف' : 'Examination')
                : (app()->getLocale() === 'ar' ? 'متابعة' : 'Follow-up'))
            ->addColumn('actions', fn ($a) => view('admin.appointments.actions', ['appointment' => $a])->render())
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Get available time slots for a clinic on a date (AJAX).
     */
    public function availableSlots(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $clinic = Clinic::findOrFail($request->clinic_id);
        $date = Carbon::parse($request->date);
        $dayName = strtolower($date->format('l')); // monday, tuesday, ...

        $wh = ClinicWorkingHour::where('clinic_id', $clinic->id)
            ->where('day', $dayName)
            ->first();

        if (!$wh || $wh->is_closed || !$wh->from || !$wh->to) {
            return response()->json(['slots' => []]);
        }

        $from = Carbon::parse($wh->from);
        $to = Carbon::parse($wh->to);
        $slots = [];
        $current = $from->copy();
        $intervalMinutes = 30;

        while ($current->format('H:i') < $to->format('H:i')) {
            $timeStr = $current->format('H:i');
            $taken = Appointment::where('clinic_id', $clinic->id)
                ->where('appointment_date', $request->date)
                ->where('appointment_time', $timeStr)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();
            $slots[] = [
                'time' => $timeStr,
                'available' => !$taken,
            ];
            $current->addMinutes($intervalMinutes);
        }

        return response()->json(['slots' => $slots]);
    }
}
