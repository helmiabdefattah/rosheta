<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class ClinicDashboardController extends Controller
{
    public function index(Request $request)
    {
        $clinic = $request->attributes->get('clinic') ?? $request->user()->managedClinic;
        abort_unless($clinic, 403);

        $appointments = Appointment::query()
            ->where('clinic_id', $clinic->id)
            ->with(['client', 'doctor'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(20)
            ->withQueryString();

        return view('clinic.dashboard', compact('clinic', 'appointments'));
    }
}
