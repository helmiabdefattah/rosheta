{{--
    Money summary for one appointment row, shared by the doctor and assistant
    dashboards: collected / total due, the extras added during the examination,
    and what's still owed.

    Expects: $appt (Appointment, with `items` and `collections` loaded).
--}}
@php
    $due = $appt->dueAmount();
    $paid = $appt->collectedAmount();
    $left = $appt->remainingAmount();
    $refund = $appt->refundDue();
    $extras = $appt->items;
@endphp

@if ($due <= 0 && $paid <= 0)
    <span class="text-xs text-slate-300">{{ __('app.collection.no_price') }}</span>
@else
    <div class="text-end md:text-start">
        <div>
            <span class="font-semibold text-slate-800">{{ number_format($paid, 2) }}</span>
            <span class="text-slate-400">/ {{ number_format($due, 2) }}</span>
            <span class="text-xs text-slate-400">{{ __('app.clinic.currency') }}</span>
        </div>

        {{-- Extras added during the examination --}}
        @if ($extras->isNotEmpty())
            <div class="text-[11px] text-slate-500 mt-0.5 leading-snug"
                 title="{{ $extras->map(fn ($i) => $i->name.' ×'.$i->quantity.' = '.number_format($i->total(), 2))->join(', ') }}">
                @foreach ($extras as $i)
                    <span class="inline-block whitespace-nowrap">
                        {{ $i->name }}@if ($i->quantity > 1)<span class="text-slate-400">×{{ $i->quantity }}</span>@endif{{ ! $loop->last ? ',' : '' }}
                    </span>
                @endforeach
            </div>
        @endif

        <div class="text-[11px] mt-0.5">
            @if ($refund > 0)
                {{-- Fee discounted after the patient had already paid --}}
                <span class="text-red-600 font-semibold">↩ {{ __('app.collection.refund_due') }}: {{ number_format($refund, 2) }}</span>
            @elseif ($left <= 0)
                <span class="text-emerald-600 font-medium">✔ {{ __('app.collection.settled') }}</span>
            @elseif ($paid > 0)
                <span class="text-amber-600 font-medium">{{ __('app.collection.remaining') }}: {{ number_format($left, 2) }}</span>
            @else
                <span class="text-slate-400">{{ __('app.collection.unpaid') }}</span>
            @endif
        </div>
    </div>
@endif
