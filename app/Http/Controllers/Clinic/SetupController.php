<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Doctor "Setup" landing: clinical templates and custom examination fields.
 */
class SetupController extends Controller
{
    use ClinicContext;

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);

        return view('clinic.doctor.setup.index', [
            'doctor' => $doctor,
            'plansCount' => $doctor->medicalPlans()->count(),
            'fieldsCount' => $doctor->examinationFields()->count(),
        ]);
    }
}
