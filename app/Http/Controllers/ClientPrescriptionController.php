<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Lets a patient (client) see the prescriptions their doctors wrote for them
 * during clinic visits, and open/print any single prescription.
 */
class ClientPrescriptionController extends Controller
{
    /** List the patient's own prescriptions (newest first). */
    public function index(): View
    {
        $client = Auth::guard('client')->user();

        $prescriptions = Prescription::where('client_id', $client->id)
            ->with(['doctor', 'appointment.clinic'])
            ->withCount('items')
            ->latest()
            ->paginate(15);

        return view('client.prescriptions.index', compact('prescriptions'));
    }

    /** Print-friendly view of one of the patient's own prescriptions. */
    public function print(Prescription $prescription): View
    {
        $client = Auth::guard('client')->user();
        abort_unless((int) $prescription->client_id === (int) $client->id, 403);

        $prescription->load(['items', 'client', 'doctor', 'diagnosis', 'appointment.clinic']);

        return view('clinic.prescriptions.print', compact('prescription'));
    }
}
