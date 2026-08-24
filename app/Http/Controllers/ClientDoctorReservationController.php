<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Area;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\ClinicDoctorWorkingHour;
use App\Models\ClinicWorkingHour;
use App\Models\Governorate;
use App\Models\Specialization;
use App\Notifications\DoctorAppointmentBookedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientDoctorReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Clinic::with(['doctor.specialization', 'doctor.user', 'doctors.specialization', 'doctors.user', 'governorate', 'city', 'area', 'workingHours', 'clinicDoctorWorkingHours']);

        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('specialization_id')) {
            $sid = $request->specialization_id;
            $query->where(function ($q) use ($sid) {
                $q->whereHas('doctor', fn ($q2) => $q2->where('specialization_id', $sid))
                    ->orWhereHas('doctors', fn ($q2) => $q2->where('specialization_id', $sid));
            });
        }

        $clinics = $query->orderBy('name')->paginate(12)->withQueryString();

        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $specializations = Specialization::orderBy('name')->get();
        $cities = $request->filled('governorate_id')
            ? City::where('governorate_id', $request->governorate_id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $areas = $request->filled('city_id')
            ? Area::where('city_id', $request->city_id)->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('client.doctor-reservation.index', compact('clinics', 'governorates', 'specializations', 'cities', 'areas'));
    }

    public function book(Clinic $clinic, Request $request)
    {
        $clinic->load(['doctor.specialization', 'doctors.specialization', 'governorate', 'city', 'area', 'workingHours', 'clinicDoctorWorkingHours']);
        $selectedDoctor = null;
        if ($request->filled('doctor_id')) {
            $doc = Doctor::find($request->doctor_id);
            if ($doc && ($clinic->doctor_id === $doc->id || $clinic->doctors()->where('doctors.id', $doc->id)->exists())) {
                $selectedDoctor = $doc->load('specialization');
            }
        }
        if (!$selectedDoctor && $clinic->doctor) {
            $selectedDoctor = $clinic->doctor;
        }
        $availableDays = $selectedDoctor
            ? $clinic->getAvailableDaysForDoctor($selectedDoctor)
            : $clinic->getAvailableDays();
        return view('client.doctor-reservation.book', compact('clinic', 'availableDays', 'selectedDoctor'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'type' => 'required|in:medical_examination,follow_up',
            'notes' => 'nullable|string|max:1000',
        ]);

        $clinic = Clinic::findOrFail($validated['clinic_id']);
        $doctorId = $validated['doctor_id'] ?? $clinic->doctor_id;
        if (!$doctorId || !($clinic->doctor_id === $doctorId || $clinic->doctors()->where('doctors.id', $doctorId)->exists())) {
            return back()->withInput()->withErrors(['doctor_id' => app()->getLocale() === 'ar' ? 'الطبيب غير مرتبط بهذه العيادة' : 'Doctor is not linked to this clinic.']);
        }
        $doctor = Doctor::find($doctorId);
        if ($doctor && $doctor->isOffOnDate($validated['appointment_date'], $clinic->id)) {
            return back()->withInput()->withErrors([
                'appointment_date' => app()->getLocale() === 'ar' ? 'الطبيب في إجازة في هذا اليوم ولا يمكن الحجز.' : 'The doctor is off on this day; booking is not available.',
            ]);
        }
        $price = $doctor ? (float) $clinic->getPriceForDoctor($doctor, $validated['type']) : ($validated['type'] === 'medical_examination' ? $clinic->medical_examination_price : $clinic->follow_up_price);

        $exists = Appointment::where('clinic_id', $validated['clinic_id'])
            ->where('doctor_id', $doctorId)
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['appointment_time' => app()->getLocale() === 'ar' ? 'هذا الموعد محجوز بالفعل' : 'This slot is already booked.']);
        }

        $appointment = Appointment::create([
            'doctor_id' => $doctorId,
            'clinic_id' => $clinic->id,
            'user_id' => null,
            'client_id' => Auth::guard('client')->id(),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'type' => $validated['type'],
            'price' => $price,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($doctor && $doctor->user_id) {
            $doctor->loadMissing('user');
            if ($doctor->user) {
                $doctor->user->notify(new DoctorAppointmentBookedNotification($appointment));
            }
        }

        return redirect()->route('client.doctor-reservation.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حجز الموعد بنجاح' : 'Appointment booked successfully');
    }

    public function availableSlots(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'date' => 'required|date|after_or_equal:today',
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        $clinic = Clinic::findOrFail($request->clinic_id);
        $date = Carbon::parse($request->date);
        $dayName = strtolower($date->format('l'));
        $doctorId = $request->doctor_id;

        if ($doctorId) {
            $doctor = \App\Models\Doctor::find($doctorId);
            if ($doctor && $doctor->isOffOnDate($request->date, $clinic->id)) {
                return response()->json(['slots' => []]);
            }
        }
        if ($doctorId) {
            $wh = ClinicDoctorWorkingHour::where('clinic_id', $clinic->id)
                ->where('doctor_id', $doctorId)
                ->where('day', $dayName)
                ->first();
            if (!$wh || $wh->is_closed || !$wh->from || !$wh->to) {
                $wh = ClinicWorkingHour::where('clinic_id', $clinic->id)->where('day', $dayName)->first();
            }
        } else {
            $wh = ClinicWorkingHour::where('clinic_id', $clinic->id)->where('day', $dayName)->first();
        }

        if (!$wh || $wh->is_closed || !$wh->from || !$wh->to) {
            return response()->json(['slots' => []]);
        }

        $from = Carbon::parse($wh->from);
        $to = Carbon::parse($wh->to);
        $slots = [];
        $current = $from->copy();
        $intervalMinutes = 30;

        $appointmentQuery = Appointment::where('clinic_id', $clinic->id)
            ->where('appointment_date', $request->date)
            ->whereIn('status', ['pending', 'confirmed']);
        if ($doctorId) {
            $appointmentQuery->where('doctor_id', $doctorId);
        }

        $cap = $clinic->getSlotsPerInterval();
        while ($current->format('H:i') < $to->format('H:i')) {
            $timeStr = $current->format('H:i');
            $count = (clone $appointmentQuery)->where('appointment_time', $timeStr)->count();
            $slots[] = ['time' => $timeStr, 'available' => $count < $cap];
            $current->addMinutes($intervalMinutes);
        }

        return response()->json(['slots' => $slots]);
    }
}
