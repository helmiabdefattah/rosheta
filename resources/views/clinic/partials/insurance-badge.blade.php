{{--
    Small insurance chip shown under a patient's name in the reservation lists.
    Expects: $appt (Appointment with `insurance.insuranceCompany` loaded).
--}}
@if ($appt->insurance && $appt->insurance->insuranceCompany)
    <div class="mt-1 inline-flex items-center gap-1 text-[11px] text-cyan-800 bg-cyan-50 border border-cyan-200 rounded px-1.5 py-0.5">
        <span>🛡</span>
        <span class="font-medium">{{ $appt->insurance->insuranceCompany->displayName() }}</span>
        @if ((float) $appt->insurance->insurance_amount > 0)
            <span class="text-cyan-600">· {{ __('app.insurance.covers') }} {{ number_format((float) $appt->insurance->insurance_amount, 2) }}</span>
        @endif
    </div>
@endif
