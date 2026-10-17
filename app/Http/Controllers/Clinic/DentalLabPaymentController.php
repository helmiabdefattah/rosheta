<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\DentalLabPaymentRequest;
use App\Models\DentalLabOrder;
use App\Models\DentalLabPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Payments from the clinic to the lab for an order (partial payments allowed).
 */
class DentalLabPaymentController extends Controller
{
    use ClinicContext;

    public function store(DentalLabPaymentRequest $request, DentalLabOrder $order): RedirectResponse
    {
        $this->guardOrder($request, $order);

        $order->payments()->create([
            'amount' => $request->input('amount'),
            'paid_at' => $request->input('paid_at') ?: now(),
            'method' => $request->input('method'),
            'note' => $request->input('note'),
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', __('app.dental.payment.added'));
    }

    public function destroy(Request $request, DentalLabPayment $payment): RedirectResponse
    {
        $order = $payment->order;
        $this->guardOrder($request, $order);

        $payment->delete();

        return back()->with('status', __('app.dental.payment.removed'));
    }

    private function guardOrder(Request $request, DentalLabOrder $order): void
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($order->clinic_id === $clinic->id, 403);
    }
}
