{{--
    The queue ticket as printed. Line for line, this is the buffer
    EscPosTicketRenderer::build() sends to the RONGTA: same order, same
    localized strings in the clinic's printer language, same QR payload, same
    relative type sizes (the printer's GS ! magnification).
--}}
@extends('demo.print.layout')

@section('title', __('app.ticket.title', ['num' => $appointment->queue_number], $lang))

@section('paper')
    {{-- Platform mark + clinic header, centred --}}
    <div class="c">
        <img src="{{ \App\Support\SiteBrand::logoUrl() }}" alt="" class="logo">
        <div class="base">{{ \App\Support\SiteBrand::name($lang) }}</div>
        <div class="lg">{{ $appointment->clinic->name ?? config('app.name') }}</div>
        @if ($appointment->doctor?->name)
            <div class="base b">{{ $appointment->doctor->name }}</div>
        @endif
        @if ($appointment->clinic?->phone_number)
            <div class="base ltr">{{ $appointment->clinic->phone_number }}</div>
        @endif
    </div>

    <hr>

    {{-- Queue number, the reason the paper exists --}}
    <div class="c">
        <div class="base">{{ __('app.ticket.queue_number', [], $lang) }}</div>
        <div class="xl">{{ $appointment->queue_number ?? '-' }}</div>
        <div class="base">
            {{ trans_choice('app.ticket.patients_ahead', $ahead, ['count' => $ahead], $lang) }}
        </div>
    </div>

    <hr>

    {{-- Visit details, read in the ticket language's direction --}}
    <div class="start base">
        <div>{{ __('app.ticket.patient', [], $lang) }}: {{ $appointment->client->name ?? '-' }}</div>
        <div>{{ __('app.ticket.type', [], $lang) }}: {{ $appointment->typeLabel($lang) }}</div>
        @if ($appointment->scheduled_at)
            <div>{{ __('app.ticket.date', [], $lang) }}: <span class="ltr">{{ $appointment->scheduled_at->format('Y-m-d') }}</span></div>
            <div>{{ __('app.ticket.time', [], $lang) }}: <span class="ltr">{{ $appointment->scheduled_at->format('H:i') }}</span></div>
        @endif
    </div>

    <hr>

    @if ($qr)
        <img src="{{ $qr }}" alt="" class="qr">
    @endif

    <div class="c base">{{ __('app.ticket.thanks', [], $lang) }}</div>
@endsection
