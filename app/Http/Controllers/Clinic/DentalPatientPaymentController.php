<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\DentalLabPaymentRequest;
use App\Models\DentalLabOrder;
use App\Models\DentalPatientPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Payments the patient makes to the clinic for a lab order (clinic receivable),
 * separate from the clinic's payments to the lab.
 */
class DentalPatientPaymentController extends Controller
{
    use ClinicContext;

    public function store(DentalLabPaymentRequest $request, DentalLabOrder $order): RedirectResponse
    {
        $this->guardOrder($request, $order);

        $order->patientPayments()->create([
            'amount' => $request->input('amount'),
            'paid_at' => $request->input('paid_at') ?: now(),
            'method' => $request->input('method'),
            'note' => $request->input('note'),
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', __('app.dental.payment.added'));
    }

    public function destroy(Request $request, DentalPatientPayment $patientPayment): RedirectResponse
    {
        $this->guardOrder($request, $patientPayment->order);

        $patientPayment->delete();

        return back()->with('status', __('app.dental.payment.removed'));
    }

    private function guardOrder(Request $request, DentalLabOrder $order): void
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($order->clinic_id === $clinic->id, 403);
    }
}
