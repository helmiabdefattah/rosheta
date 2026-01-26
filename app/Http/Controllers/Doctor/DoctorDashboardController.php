<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use Illuminate\Http\Request;

class DoctorDashboardController extends Controller
{
    protected function doctor(Request $request)
    {
        return $request->attributes->get('doctor') ?? auth()->user()->doctor;
    }

    public function index(Request $request)
    {
        $doctor = $this->doctor($request);
        $doctor->load(['specialization', 'clinics.doctor', 'clinics.doctors']);
        $clinicsWhereDoctor = Clinic::where('doctor_id', $doctor->id)
            ->orWhereHas('doctors', fn ($q) => $q->where('doctors.id', $doctor->id))
            ->with(['governorate', 'city', 'area'])
            ->get();
        $upcomingAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['clinic', 'client'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get();
        $todayCount = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        return view('doctor.dashboard', compact('doctor', 'clinicsWhereDoctor', 'upcomingAppointments', 'todayCount'));
    }
}
