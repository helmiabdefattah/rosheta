{{--
    The prescription as printed on the clinic's thermal roll.

    Same content, same order and same printer-language labels as the data-only
    FCM message PrintPrescriptionNotification sends to the staff app — which is
    what actually drives the printer outside the demo.
--}}
@extends('demo.print.layout')

@section('title', __('app.print.title', ['code' => $prescription->code], $lang))

@section('paper')
    {{-- Platform mark + clinic header --}}
    <div class="c">
        <img src="{{ \App\Support\SiteBrand::logoUrl() }}" alt="" class="logo">
        <div class="sm">{{ \App\Support\SiteBrand::name($lang) }}</div>
        <div class="lg">{{ $clinic->name ?? config('app.name') }}</div>
        @if ($clinic?->address)
            <div class="sm">{{ $clinic->address }}</div>
        @endif
        @if ($clinic?->phone_number)
            <div class="sm ltr">{{ $clinic->phone_number }}</div>
        @endif
    </div>

    <hr>

    <div class="c">
        <div class="base b">{{ __('app.print.doctor', [], $lang) }}: {{ $prescription->doctor?->name }}</div>
        @if ($prescription->doctor?->specialization?->name)
            <div class="sm">{{ $prescription->doctor->specialization->name }}</div>
        @endif
    </div>

    <hr>

    {{-- Who it is for, and when --}}
    <div class="start">
        <div class="row">
            <span>{{ __('app.print.patient', [], $lang) }}</span>
            <span class="v">{{ $prescription->client?->name ?? '-' }}</span>
        </div>
        @if ($prescription->client?->age)
            <div class="row">
                <span>{{ __('app.print.age', [], $lang) }}</span>
                <span class="v">
                    {{ $prescription->client->age }} {{ __('app.common.yrs', [], $lang) }}
                    @if ($prescription->client->gender)
                        — {{ __('app.genders.'.$prescription->client->gender, [], $lang) }}
                    @endif
                </span>
            </div>
        @endif
        <div class="row">
            <span>{{ __('app.ticket.date', [], $lang) }}</span>
            <span class="v ltr">{{ optional($prescription->created_at)->format('Y-m-d') }}</span>
        </div>
        <div class="row">
            <span>#</span>
            <span class="v ltr">{{ $prescription->code }}</span>
        </div>
    </div>

    @if ($prescription->diagnosis?->diagnosis)
        <hr>
        <div class="start base">
            <span class="b">{{ __('app.common.diagnosis', [], $lang) }}:</span>
            {{ $prescription->diagnosis->diagnosis }}
        </div>
    @endif

    <hr>

    {{-- Medicines: one numbered block each, the alternative under its medicine --}}
    <div class="c base b">℞ {{ __('app.print.rx_title', [], $lang) }}</div>

    <div class="start">
        @forelse ($prescription->items as $i => $item)
            <div class="item">
                <div class="base b"><span class="ltr">{{ $i + 1 }}.</span> {{ $item->medicine_name }}</div>
                <div class="sm">
                    @if ($item->dose)<div>{{ __('app.print.dose', [], $lang) }}: {{ $item->dose }}</div>@endif
                    @if ($item->frequency)<div>{{ __('app.print.frequency', [], $lang) }}: {{ $item->frequency }}</div>@endif
                    @if ($item->duration)<div>{{ __('app.print.duration', [], $lang) }}: {{ $item->duration }}</div>@endif
                    @if ($item->instructions)<div>{{ __('app.print.instructions', [], $lang) }}: {{ $item->instructions }}</div>@endif
                </div>

                @if ($item->substitute_name)
                    <div class="sub">
                        <div class="b">↔ {{ __('app.print.substitute', [], $lang) }}: {{ $item->substitute_name }}</div>
                        @if ($item->substitute_dose)<div>{{ __('app.print.dose', [], $lang) }}: {{ $item->substitute_dose }}</div>@endif
                        @if ($item->substitute_frequency)<div>{{ __('app.print.frequency', [], $lang) }}: {{ $item->substitute_frequency }}</div>@endif
                        @if ($item->substitute_duration)<div>{{ __('app.print.duration', [], $lang) }}: {{ $item->substitute_duration }}</div>@endif
                        @if ($item->substitute_instructions)<div>{{ __('app.print.instructions', [], $lang) }}: {{ $item->substitute_instructions }}</div>@endif
                    </div>
                @endif
            </div>
        @empty
            <div class="sm c">—</div>
        @endforelse
    </div>

    {{-- Examinations / lab / radiology ordered during the same visit --}}
    @php($requests = $prescription->appointment?->medicalRequests ?? collect())
    @if ($requests->isNotEmpty())
        <hr>
        <div class="c base b">{{ __('app.print.requests_title', [], $lang) }}</div>
        <div class="start sm">
            @foreach ($requests as $r)
                <div class="item">
                    <div class="b"><span class="ltr">{{ $loop->iteration }}.</span> {{ $r->name }}</div>
                    <div>{{ __('app.request_types.'.$r->type, [], $lang) }}</div>
                    @if ($r->notes)<div>{{ $r->notes }}</div>@endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($prescription->sick_leave_days)
        <hr>
        <div class="start base">
            <span class="b">{{ __('app.print.sick_leave', [], $lang) }}:</span>
            {{ trans_choice('app.print.sick_leave_days', $prescription->sick_leave_days, ['count' => $prescription->sick_leave_days], $lang) }}
        </div>
    @endif

    @if ($prescription->notes)
        <hr>
        <div class="start sm">
            <span class="b">{{ __('app.common.notes', [], $lang) }}:</span>
            {{ $prescription->notes }}
        </div>
    @endif

    <hr>

    @if ($qr)
        <img src="{{ $qr }}" alt="" class="qr">
        <div class="c sm">{{ __('app.print.scan_hint', [], $lang) }}</div>
    @endif

    <div class="c sm" style="margin-top:10px">{{ __('app.print.signature', [], $lang) }}</div>
    <div class="c sm">........................</div>
@endsection
