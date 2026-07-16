<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Front-desk money handling for an appointment: recording what the patient
 * paid, and correcting the visit type (which re-prices the visit).
 */
class CollectionController extends Controller
{
    use ClinicContext;

    /** Record a payment taken from the patient. */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($request, $appointment);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        Collection::create([
            'appointment_id' => $appointment->id,
            'amount' => $data['amount'],
            'collected_by' => $request->user()?->id,
            'collected_at' => now(),
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('status', __('app.collection.recorded', [
            'amount' => number_format((float) $data['amount'], 2),
        ]));
    }

    /** Undo a payment entry (mis-keyed amount, refund at the desk). */
    public function destroy(Request $request, Collection $collection): RedirectResponse
    {
        $this->authorizeAppointment($request, $collection->appointment);
        $collection->delete();

        return back()->with('status', __('app.collection.deleted'));
    }

    /**
     * Change the visit type. The visit is re-priced from the clinic's current
     * settings for the new type; payments already taken are left alone, since
     * that money really changed hands.
     */
    public function updateType(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($request, $appointment);

        $type = $request->validate([
            'type' => ['required', 'in:examination,consultation'],
        ])['type'];

        $appointment->update([
            'type' => $type,
            'price' => $appointment->clinic?->priceFor($type),
        ]);

        return back()->with('status', __('app.appointment.type_changed', [
            'type' => $appointment->typeLabel(),
        ]));
    }

    protected function authorizeAppointment(Request $request, Appointment $appointment): void
    {
        abort_unless($appointment->doctor_id === $this->clinicDoctor($request)->id, 403);
    }
}
