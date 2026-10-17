<?php

namespace App\Observers;

use App\Models\DentalLabPayment;
use App\Support\DentalLabExpenseCapture;

/**
 * Keeps the expenses ledger in step with clinic→lab payments when the clinic
 * has opted into auto-capture. All the gating lives in the capture service, so
 * this observer is a thin bridge.
 */
class DentalLabPaymentObserver
{
    public function __construct(private DentalLabExpenseCapture $capture)
    {
    }

    public function created(DentalLabPayment $payment): void
    {
        $this->capture->capture($payment);
    }

    public function deleted(DentalLabPayment $payment): void
    {
        $this->capture->remove($payment);
    }
}
