{{--
    Both eyes, as the doctor faces the patient: the patient's right eye on the
    viewer's left.

    Unlike the other charts a note here is not attached to the whole part but to
    a point on it — the doctor clicks where the finding is and the note is
    pinned there, so a lesion at 2 o'clock is recorded as such. The script reads
    the click as a fraction of the hit area's box, which keeps the pin correct
    however the SVG is scaled.
--}}
@php
    $labels = \App\Support\ClinicalChart::regions(\App\Support\ClinicalChart::EYES);

    // cx of each eye inside the 320-wide canvas.
    $eyes = [
        ['eye-right', 84],
        ['eye-left', 236],
    ];
@endphp

<div class="flex justify-center">
    <svg viewBox="0 0 320 170" class="w-full max-w-[420px]" role="group"
         aria-label="{{ __('app.chart.eyes_title') }}">
        @foreach ($eyes as [$key, $cx])
            <text x="{{ $cx }}" y="16" text-anchor="middle" font-size="11" fill="#94a3b8">
                {{ $labels[$key] }}
            </text>

            <g data-region="{{ $key }}" role="button" tabindex="0" aria-label="{{ $labels[$key] }}">
                <title>{{ $labels[$key] }}</title>

                {{-- Sclera. `part` is what the selected/has-notes styles colour. --}}
                <circle class="part" cx="{{ $cx }}" cy="90" r="58"
                        fill="#f8fafc" stroke="#94a3b8" stroke-width="1.5"/>

                {{-- Iris and pupil are decoration: pointer-events stay on the
                     group, so a click on the pupil still yields its own x/y. --}}
                <circle cx="{{ $cx }}" cy="90" r="26" fill="#bae6fd" stroke="#7dd3fc" pointer-events="none"/>
                <circle cx="{{ $cx }}" cy="90" r="11" fill="#1e293b" pointer-events="none"/>
                <circle cx="{{ $cx - 9 }}" cy="80" r="4" fill="#ffffff" opacity=".85" pointer-events="none"/>

                {{-- Pins are appended here, positioned in % of this box. --}}
                <svg data-pin-layer="{{ $key }}" x="{{ $cx - 58 }}" y="32" width="116" height="116"
                     overflow="visible" pointer-events="none"></svg>
            </g>
        @endforeach
    </svg>
</div>
