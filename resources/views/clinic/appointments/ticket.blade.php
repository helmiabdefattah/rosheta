@php
    // Print the ticket in the clinic's configured printer language, not the
    // locale of whoever opened the page — otherwise a clinic set to Arabic
    // still prints English tickets (and vice-versa).
    app()->setLocale($appointment->clinic?->printerLanguage() ?? app()->getLocale());
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('app.ticket.title', ['num' => $appointment->queue_number]) }}</title>
    <style>
        :root { --w: 80mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f1f5f9;
            font-family: "Courier New", ui-monospace, monospace;
            color: #0f172a;
        }
        .toolbar {
            max-width: var(--w);
            margin: 16px auto 8px;
            display: flex;
            justify-content: space-between;
            font-family: system-ui, sans-serif;
            font-size: 13px;
        }
        .toolbar a { color: #4f46e5; text-decoration: none; }
        .toolbar button {
            background: #7c3aed; color: #fff; border: 0;
            padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px;
        }
        .ticket {
            width: var(--w);
            margin: 0 auto 24px;
            background: #fff;
            padding: 6mm 5mm;
            box-shadow: 0 1px 4px rgba(0,0,0,.12);
        }
        .center { text-align: center; }
        .clinic { font-size: 15px; font-weight: 700; }
        .doctor { font-size: 13px; font-weight: 700; color: #4338ca; margin-top: 2px; }
        .muted { color: #475569; font-size: 11px; }
        hr { border: 0; border-top: 1px dashed #94a3b8; margin: 8px 0; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #64748b; }
        .num-label { font-size: 11px; letter-spacing: 1px; color: #475569; }
        .num { font-size: 52px; font-weight: 800; line-height: 1; margin: 2px 0 4px; }
        .row { display: flex; justify-content: space-between; gap: 8px; font-size: 12px; padding: 2px 0; }
        .row .v { font-weight: 700; text-align: end; }
        .name { font-size: 15px; font-weight: 700; }
        .footer { font-size: 11px; }
        .booked { margin-top: 8px; text-align: center; font-size: 11px; font-weight: 700; color: #0f766e; }

        @media print {
            @page { size: 80mm auto; margin: 0; }
            body { background: #fff; }
            .toolbar { display: none !important; }
            .ticket { box-shadow: none; margin: 0; width: auto; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ url()->previous() }}">{{ __('app.common.back') }}</a>
        <button onclick="window.print()">{{ __('app.common.print') }}</button>
    </div>

    <div class="ticket">
        {{-- Clinic header --}}
        <div class="center">
            <div class="clinic">🩺 {{ $appointment->clinic->name ?? config('app.name') }}</div>
            @if($appointment->doctor)
                <div class="doctor">{{ $appointment->doctor->name }}</div>
            @endif
            @if($appointment->clinic?->address)
                <div class="muted">{{ $appointment->clinic->address }}</div>
            @endif
            @if($appointment->clinic?->phone_number)
                <div class="muted">{{ $appointment->clinic->phone_number }}</div>
            @endif
        </div>

        <hr>

        {{-- Queue number --}}
        <div class="center">
            <div class="num-label">{{ __('app.ticket.queue_number') }}</div>
            <div class="num">{{ $appointment->queue_number ?? '—' }}</div>
            @php $ahead = $appointment->patientsWaitingAhead(); @endphp
            <div class="muted">
                {{ trans_choice('app.ticket.patients_ahead', $ahead, ['count' => $ahead]) }}
            </div>
        </div>

        <hr>

        {{-- Patient --}}
        <div class="label">{{ __('app.ticket.patient') }}</div>
        <div class="name">{{ $appointment->client->name }}</div>
        @if($appointment->client->phone_number)
            <div class="row"><span>{{ __('app.common.phone') }}</span><span class="v">{{ $appointment->client->phone_number }}</span></div>
        @endif
        <div class="row">
            <span>{{ __('app.common.gender') }}</span>
            <span class="v">
                {{ $appointment->client->gender ? __('app.genders.'.$appointment->client->gender) : '—' }}
                @if($appointment->client->age) &middot; {{ $appointment->client->age }} {{ __('app.common.yrs') }} @endif
            </span>
        </div>

        <hr>

        {{-- Appointment --}}
        <div class="row"><span>{{ __('app.ticket.type') }}</span><span class="v">{{ $appointment->typeLabel() }}</span></div>
        <div class="row"><span>{{ __('app.ticket.date') }}</span><span class="v">{{ $appointment->scheduled_at->translatedFormat('d M Y') }}</span></div>
        <div class="row"><span>{{ __('app.ticket.time') }}</span><span class="v">{{ $appointment->scheduled_at->format('H:i') }}</span></div>

        <hr>

        <div class="center footer muted">{{ __('app.ticket.thanks') }}</div>
    </div>

    <script>
        // If we arrived here from the waiting-room display's check-in button,
        // return to that display once the ticket has been printed (or the print
        // dialog dismissed). The URL was stashed in sessionStorage before the
        // kiosk flow started; consume it so a manual reprint won't redirect.
        (function () {
            var returnUrl = null;
            try { returnUrl = sessionStorage.getItem('kioskReturnUrl'); } catch (e) {}
            if (returnUrl) {
                try { sessionStorage.removeItem('kioskReturnUrl'); } catch (e) {}
                window.addEventListener('afterprint', function () {
                    location.href = returnUrl;
                });
            }

            // Auto-open the print dialog (skip with ?auto=0).
            if (new URLSearchParams(location.search).get('auto') !== '0') window.print();
        })();
    </script>
</body>
</html>
