<?php

namespace App\Http\Controllers\Doctor;

use App\Models\Appointment;
use App\Models\BonusPoint;
use Illuminate\Http\Request;

class DoctorAppointmentController extends DoctorDashboardController
{
    public function index(Request $request)
    {
        $doctor = $this->doctor($request);
        $query = Appointment::where('doctor_id', $doctor->id)
            ->with(['clinic', 'client'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->where('appointment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('appointment_date', '<=', $request->to_date);
        }
        if ($request->filled('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }
        $appointments = $query->paginate(20)->withQueryString();
        return view('doctor.appointments.index', compact('doctor', 'appointments'));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $doctor = $this->doctor($request);
        if ($appointment->doctor_id !== $doctor->id) {
            abort(403);
        }
        $request->validate(['status' => 'required|in:confirmed,completed,missed,cancelled']);
        $appointment->update(['status' => $request->status]);

        if ($request->status === 'completed' && $appointment->client_id) {
            $points = (int) round((float) $appointment->price);
            if ($points > 0) {
                BonusPoint::awardUnique(
                    clientId: (int) $appointment->client_id,
                    sourceType: 'appointment',
                    sourceId: (int) $appointment->id,
                    points: $points
                );
            }
        }

        $messages = [
            'confirmed' => [ 'ar' => 'تم تأكيد الموعد', 'en' => 'Appointment confirmed.' ],
            'completed' => [ 'ar' => 'تم إتمام الموعد', 'en' => 'Appointment completed.' ],
            'missed' => [ 'ar' => 'تم تعليم الموعد كفائت', 'en' => 'Appointment marked as missed.' ],
            'cancelled' => [ 'ar' => 'تم إلغاء الموعد', 'en' => 'Appointment cancelled.' ],
        ];
        $msg = app()->getLocale() === 'ar' ? ($messages[$request->status]['ar'] ?? $request->status) : ($messages[$request->status]['en'] ?? $request->status);
        return back()->with('success', $msg);
    }
}
