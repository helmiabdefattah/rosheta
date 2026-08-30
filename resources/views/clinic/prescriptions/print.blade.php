@php
    $isAr = app()->getLocale() === 'ar';
    $clinic = $prescription->appointment?->clinic;
    $client = $prescription->client;
    $doctor = $prescription->doctor;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.print.title', ['code' => $prescription->code]) }}</title>
    <style>
        :root { --ink:#1e293b; --teal:#0d9488; --teal-d:#0f766e; --muted:#64748b; --line:#cbd5e1; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            background: #eef2f5;
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, system-ui, -apple-system, Arial, sans-serif;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .toolbar {
            max-width: 148mm; margin: 12px auto 8px; display: flex; justify-content: space-between; gap: 8px;
        }
        .toolbar a { color: var(--teal-d); text-decoration: none; font-size: 13px; align-self: center; }
        .toolbar button {
            background: var(--teal); color: #fff; border: 0; padding: 9px 18px;
            border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600;
        }
        /* The sheet: A5 portrait. */
        .sheet {
            width: 148mm; min-height: 210mm; margin: 0 auto 24px; background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,.12); position: relative;
            display: flex; flex-direction: column; overflow: hidden;
        }
        .rx-header {
            background: linear-gradient(135deg, var(--teal) 0%, var(--teal-d) 100%);
            color: #fff; padding: 14mm 12mm 8mm; display: flex; justify-content: space-between; gap: 10px;
        }
        .rx-header .doc-name { font-size: 20px; font-weight: 800; line-height: 1.2; }
        .rx-header .doc-spec { font-size: 12px; opacity: .92; margin-top: 3px; }
        .rx-header .clinic { font-size: 12px; opacity: .92; margin-top: 6px; }
        .rx-header .meta { text-align: end; font-size: 11px; opacity: .95; white-space: nowrap; }
        .rx-header .code { font-family: ui-monospace, monospace; background: rgba(255,255,255,.18); padding: 2px 8px; border-radius: 6px; }
        .body { padding: 8mm 12mm; flex: 1; }
        .patient {
            display: flex; flex-wrap: wrap; gap: 4px 20px; font-size: 13px;
            border: 1px solid var(--line); border-radius: 10px; padding: 8px 12px; margin-bottom: 10px;
        }
        .patient .lbl { color: var(--muted); font-size: 11px; }
        .patient .val { font-weight: 700; }
        .dx { font-size: 12.5px; margin: 6px 2px 10px; }
        .dx .lbl { color: var(--muted); font-size: 11px; }
        .rx-mark { font-size: 40px; color: var(--teal-d); font-weight: 700; line-height: 1; font-family: Georgia, "Times New Roman", serif; }
        ol.meds { list-style: none; margin: 4px 0 0; padding: 0; counter-reset: med; }
        ol.meds > li {
            counter-increment: med; position: relative; padding: 8px 0 8px 0;
            border-bottom: 1px dashed var(--line);
        }
        ol.meds > li::before {
            content: counter(med); position: absolute; inset-inline-start: 0; top: 8px;
            width: 22px; height: 22px; border-radius: 999px; background: var(--teal);
            color: #fff; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center;
        }
        .med-row { padding-inline-start: 32px; }
        .med-name { font-size: 15.5px; font-weight: 700; }
        .med-sub {
            font-size: 12.5px; color: var(--teal-d); margin-top: 2px;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .med-sub .chip { background: #ccfbf1; border-radius: 6px; padding: 1px 6px; font-weight: 700; }
        .med-meta { font-size: 12px; color: var(--muted); margin-top: 3px; display: flex; flex-wrap: wrap; gap: 4px 14px; }
        .med-meta b { color: var(--ink); font-weight: 600; }
        .sub-meta { padding-inline-start: 14px; }
        .notes { margin-top: 12px; font-size: 12.5px; }
        .notes .lbl { color: var(--muted); font-size: 11px; }
        .requests { margin-top: 14px; border: 1px solid var(--line); border-radius: 10px; padding: 8px 12px; }
        .requests-title { font-size: 12px; font-weight: 700; color: var(--teal-d); margin-bottom: 4px; }
        .requests-group { margin-top: 4px; }
        .requests-type { font-size: 11px; color: var(--muted); }
        .requests ul { margin: 2px 0 0; padding-inline-start: 18px; font-size: 12.5px; }
        .requests li { margin: 1px 0; }
        .requests-note { color: var(--muted); font-size: 11.5px; }
        .footer .qr { text-align: center; font-size: 9px; color: var(--muted); line-height: 1.3; }
        .footer .qr img { display: block; width: 20mm; height: 20mm; margin: 0 auto 2px; }
        .footer .brand { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
        .footer .brand img { height: 12mm; width: auto; }
        .footer .brand .name { font-size: 11px; font-weight: 700; color: var(--teal-d); line-height: 1.2; }
        .footer {
            margin-top: auto; padding: 6mm 12mm 10mm; border-top: 1px solid var(--line);
            display: flex; justify-content: space-between; align-items: flex-end; gap: 12px;
        }
        .footer .contact { font-size: 11px; color: var(--muted); line-height: 1.6; }
        .footer .sign { text-align: center; font-size: 12px; color: var(--muted); }
        .footer .sign .rule { border-top: 1px solid var(--muted); width: 46mm; padding-top: 4px; margin-top: 22px; }

        @media print {
            @page { size: A5 portrait; margin: 0; }
            /* Full page height, so the sheet's flex column still stretches and
               `.footer { margin-top: auto }` pins the footer to the bottom of the
               paper exactly as it sits in the on-screen preview. `min-height:auto`
               collapsed the column, which left the footer floating up under the
               last medicine. */
            html, body { height: 100%; background: #fff; }
            .toolbar { display: none !important; }
            .sheet { box-shadow: none; margin: 0; width: auto; min-height: 100%; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ url()->previous() }}">← {{ __('app.common.back') }}</a>
        <button onclick="window.print()">🖨️ {{ __('app.print.print_pdf') }}</button>
    </div>

    <div class="sheet">
        {{-- Letterhead --}}
        <div class="rx-header">
            <div>
                <div class="doc-name">{{ $isAr ? 'د. ' : 'Dr. ' }}{{ $doctor->name ?? '' }}</div>
                @if ($doctor?->specialization?->name)
                    <div class="doc-spec">{{ $doctor->specialization->name }}</div>
                @endif
                @if ($clinic)
                    <div class="clinic">🩺 {{ $clinic->name }}</div>
                @endif
            </div>
            <div class="meta">
                <div class="code">{{ $prescription->code }}</div>
                <div style="margin-top:6px">{{ $prescription->created_at->translatedFormat('d M Y') }}</div>
            </div>
        </div>

        <div class="body">
            {{-- Patient --}}
            <div class="patient">
                <div>
                    <div class="lbl">{{ __('app.print.patient') }}</div>
                    <div class="val">{{ $client->name }}</div>
                </div>
                @if ($client->gender)
                    <div>
                        <div class="lbl">{{ __('app.common.gender') }}</div>
                        <div class="val">{{ __('app.genders.'.$client->gender) }}</div>
                    </div>
                @endif
                @if ($client->age)
                    <div>
                        <div class="lbl">{{ __('app.print.age') }}</div>
                        <div class="val">{{ $client->age }} {{ __('app.common.yrs') }}</div>
                    </div>
                @endif
            </div>

            @if ($prescription->diagnosis)
                <div class="dx">
                    <span class="lbl">{{ __('app.common.diagnosis') }}:</span>
                    {{ $prescription->diagnosis->diagnosis }}
                </div>
            @endif

            {{-- Rx --}}
            <div class="rx-mark">℞</div>
            <ol class="meds">
                @foreach ($prescription->items as $item)
                    <li>
                        <div class="med-row">
                            <div class="med-name">{{ $item->medicine_name }}</div>
                            @if ($item->substitute_name)
                                <div class="med-sub">
                                    ↔ <span>{{ __('app.print.substitute') }}:</span>
                                    <span class="chip">{{ $item->substitute_name }}</span>
                                </div>
                                @if ($item->substitute_dose || $item->substitute_frequency || $item->substitute_duration || $item->substitute_instructions)
                                    <div class="med-meta sub-meta">
                                        @if ($item->substitute_dose)<span><b>{{ __('app.print.dose') }}:</b> {{ $item->substitute_dose }}</span>@endif
                                        @if ($item->substitute_frequency)<span><b>{{ __('app.print.frequency') }}:</b> {{ $item->substitute_frequency }}</span>@endif
                                        @if ($item->substitute_duration)<span><b>{{ __('app.print.duration') }}:</b> {{ $item->substitute_duration }}</span>@endif
                                        @if ($item->substitute_instructions)<span><b>{{ __('app.print.instructions') }}:</b> {{ $item->substitute_instructions }}</span>@endif
                                    </div>
                                @endif
                            @endif
                            @if ($item->dose || $item->frequency || $item->duration || $item->instructions)
                                <div class="med-meta">
                                    @if ($item->dose)<span><b>{{ __('app.print.dose') }}:</b> {{ $item->dose }}</span>@endif
                                    @if ($item->frequency)<span><b>{{ __('app.print.frequency') }}:</b> {{ $item->frequency }}</span>@endif
                                    @if ($item->duration)<span><b>{{ __('app.print.duration') }}:</b> {{ $item->duration }}</span>@endif
                                    @if ($item->instructions)<span><b>{{ __('app.print.instructions') }}:</b> {{ $item->instructions }}</span>@endif
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>

            @include('clinic.prescriptions.partials.requests')

            @if ($prescription->notes)
                <div class="notes">
                    <div class="lbl">{{ __('app.common.notes') }}</div>
                    <div>{{ $prescription->notes }}</div>
                </div>
            @endif
        </div>

        {{-- Footer: clinic contact + signature --}}
        <div class="footer">
            <div class="contact">
                {{-- Issued through the platform; the clinic stays the letterhead. --}}
                <div class="brand">
                    <img src="{{ \App\Support\SiteBrand::logoUrl() }}" alt="">
                    <span class="name">{{ \App\Support\SiteBrand::name() }}</span>
                </div>
                @if ($clinic?->address)<div>📍 {{ $clinic->address }}</div>@endif
                @if ($clinic?->phone_number)<div>📞 {{ $clinic->phone_number }}</div>@endif
            </div>
            @php $rxQr = \App\Support\LandingQrCode::dataUri(); @endphp
            @if ($rxQr)
                <div class="qr">
                    <img src="{{ $rxQr }}" alt="">
                    {{ __('app.print.scan_hint') }}
                </div>
            @endif
            <div class="sign">
                <div class="rule">{{ __('app.print.signature') }}</div>
            </div>
        </div>
    </div>

    <script>
        // Auto-open the print dialog when arriving with ?auto=1
        if (new URLSearchParams(location.search).get('auto')) window.print();
    </script>
</body>
</html>
