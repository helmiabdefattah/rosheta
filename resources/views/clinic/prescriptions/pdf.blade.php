@php
    $isAr = app()->getLocale() === 'ar';
    $clinic = $prescription->appointment?->clinic;
    $client = $prescription->client;
    $doctor = $prescription->doctor;
    $dir = $isAr ? 'rtl' : 'ltr';
    $start = $isAr ? 'right' : 'left';
    $end = $isAr ? 'left' : 'right';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* mpdf-friendly: tables + block layout, no flex/grid. */
        body { font-family: dejavusans, sans-serif; color: #1e293b; font-size: 11px; }
        .header-td { background-color: #0d9488; color: #ffffff; padding: 14px 16px; }
        .doc-name { font-size: 17px; font-weight: bold; }
        .doc-spec { font-size: 11px; }
        .clinic { font-size: 11px; }
        .code { font-family: monospace; font-size: 11px; }
        .section { padding: 4px 16px; }
        .box {
            border: 0.6px solid #cbd5e1; border-radius: 6px; padding: 6px 10px; margin: 8px 16px;
        }
        .lbl { color: #64748b; font-size: 9px; }
        .val { font-weight: bold; font-size: 12px; }
        .rx-mark { font-size: 30px; color: #0f766e; font-weight: bold; padding: 4px 16px 0; }
        .med { padding: 6px 16px; border-bottom: 0.5px dashed #cbd5e1; }
        .med-num {
            background-color: #0d9488; color: #fff; border-radius: 10px;
            padding: 1px 6px; font-size: 10px; font-weight: bold;
        }
        .med-name { font-size: 13px; font-weight: bold; }
        .med-sub { font-size: 11px; color: #0f766e; margin-top: 2px; }
        .chip { background-color: #ccfbf1; border-radius: 4px; padding: 1px 5px; font-weight: bold; }
        .med-meta { font-size: 10px; color: #64748b; margin-top: 2px; }
        .med-meta b { color: #1e293b; }
        .notes { padding: 8px 16px; font-size: 11px; }
        .requests { margin: 10px 16px 0; border: 0.6px solid #cbd5e1; padding: 6px 10px; }
        .requests-title { font-size: 10.5px; font-weight: bold; color: #0f766e; }
        .requests-type { font-size: 9.5px; color: #64748b; margin-top: 3px; }
        .requests ul { margin: 2px 0 0; padding: 0 14px; font-size: 10.5px; }
        .requests-note { color: #64748b; font-size: 9.5px; }
        .qr-cell { text-align: center; font-size: 8px; color: #64748b; }
        .qr-cell img { width: 62px; height: 62px; }
        .brand-logo { height: 34px; }
        .brand-name { font-size: 9px; font-weight: bold; color: #0f766e; }
        .footer-td { border-top: 0.6px solid #cbd5e1; padding: 10px 16px; font-size: 9px; color: #64748b; }
        .sign { border-top: 0.6px solid #64748b; padding-top: 3px; width: 150px; text-align: center; color: #64748b; }
    </style>
</head>
<body dir="{{ $dir }}">
    {{-- Letterhead --}}
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="header-td" align="{{ $start }}">
                <div class="doc-name">{{ $isAr ? 'د. ' : 'Dr. ' }}{{ $doctor->name ?? '' }}</div>
                @if ($doctor?->specialization?->name)
                    <div class="doc-spec">{{ $doctor->specialization->name }}</div>
                @endif
                @if ($clinic)
                    <div class="clinic">{{ $clinic->name }}</div>
                @endif
            </td>
            <td class="header-td" align="{{ $end }}" valign="top">
                <div class="code">{{ $prescription->code }}</div>
                <div>{{ $prescription->created_at->translatedFormat('d M Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- Patient --}}
    <div class="box">
        <table width="100%"><tr>
            <td align="{{ $start }}">
                <span class="lbl">{{ __('app.print.patient') }}:</span>
                <span class="val">{{ $client->name }}</span>
            </td>
            <td align="{{ $end }}">
                @if ($client->gender)<span class="lbl">{{ __('app.common.gender') }}:</span> {{ __('app.genders.'.$client->gender) }} @endif
                @if ($client->age) &nbsp; <span class="lbl">{{ __('app.print.age') }}:</span> {{ $client->age }} {{ __('app.common.yrs') }} @endif
            </td>
        </tr></table>
    </div>

    @if ($prescription->diagnosis)
        <div class="section" align="{{ $start }}">
            <span class="lbl">{{ __('app.common.diagnosis') }}:</span> {{ $prescription->diagnosis->diagnosis }}
        </div>
    @endif

    {{-- Rx --}}
    <div class="rx-mark" align="{{ $start }}">&#8478;</div>
    @foreach ($prescription->items as $item)
        <div class="med">
            <span class="med-num">{{ $loop->iteration }}</span>
            <span class="med-name">{{ $item->medicine_name }}</span>
            @if ($item->substitute_name)
                <div class="med-sub">&#8596; {{ __('app.print.substitute') }}: <span class="chip">{{ $item->substitute_name }}</span></div>
                @if ($item->substitute_dose || $item->substitute_frequency || $item->substitute_duration || $item->substitute_instructions)
                    <div class="med-meta" style="padding-{{ $start }}: 12px;">
                        @if ($item->substitute_dose)<b>{{ __('app.print.dose') }}:</b> {{ $item->substitute_dose }} @endif
                        @if ($item->substitute_frequency)<b>{{ __('app.print.frequency') }}:</b> {{ $item->substitute_frequency }} @endif
                        @if ($item->substitute_duration)<b>{{ __('app.print.duration') }}:</b> {{ $item->substitute_duration }} @endif
                        @if ($item->substitute_instructions)<b>{{ __('app.print.instructions') }}:</b> {{ $item->substitute_instructions }}@endif
                    </div>
                @endif
            @endif
            @if ($item->dose || $item->frequency || $item->duration || $item->instructions)
                <div class="med-meta">
                    @if ($item->dose)<b>{{ __('app.print.dose') }}:</b> {{ $item->dose }} &nbsp; @endif
                    @if ($item->frequency)<b>{{ __('app.print.frequency') }}:</b> {{ $item->frequency }} &nbsp; @endif
                    @if ($item->duration)<b>{{ __('app.print.duration') }}:</b> {{ $item->duration }} &nbsp; @endif
                    @if ($item->instructions)<b>{{ __('app.print.instructions') }}:</b> {{ $item->instructions }}@endif
                </div>
            @endif
        </div>
    @endforeach

    @include('clinic.prescriptions.partials.requests')

    @if ($prescription->notes)
        <div class="notes" align="{{ $start }}">
            <span class="lbl">{{ __('app.common.notes') }}:</span> {{ $prescription->notes }}
        </div>
    @endif

    {{-- Footer --}}
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 24px;">
        <tr>
            <td class="footer-td" align="{{ $start }}" valign="bottom">
                {{-- Issued through the platform; the clinic stays the letterhead. --}}
                @php $rxBrandLogo = App\Support\SiteBrand::logoDataUri(); @endphp
                @if ($rxBrandLogo)
                    <div><img src="{{ $rxBrandLogo }}" class="brand-logo" alt=""></div>
                @endif
                <div class="brand-name">{{ App\Support\SiteBrand::name() }}</div>
                @if ($clinic?->address)<div>{{ $clinic->address }}</div>@endif
                @if ($clinic?->phone_number)<div>{{ $clinic->phone_number }}</div>@endif
            </td>
            @php $rxQr = App\Support\LandingQrCode::dataUri(); @endphp
            @if ($rxQr)
                <td class="footer-td qr-cell" valign="bottom" width="90">
                    <img src="{{ $rxQr }}" alt=""><br>
                    {{ __('app.print.scan_hint') }}
                </td>
            @endif
            <td class="footer-td" align="{{ $end }}" valign="bottom">
                <div class="sign" style="margin-{{ $start }}: auto;">{{ __('app.print.signature') }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
