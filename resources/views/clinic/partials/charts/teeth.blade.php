{{--
    Adult dentition in FDI notation — the numbering dentists already write in,
    so the labels need no translating.

    Quadrants 1 and 2 form the upper arch, 4 and 3 the lower. Within a quadrant
    the count runs 1 (central incisor) to 8 (third molar), so the upper-right
    quadrant is drawn reversed to put tooth 11 at the midline, exactly as it
    sits in the mouth.
--}}
@php
    // Left-to-right across the page, as the doctor faces the patient.
    $upper = array_merge(array_reverse(range(1, 8)), range(1, 8));   // 18..11, 21..28
    $lower = array_merge(array_reverse(range(1, 8)), range(1, 8));   // 48..41, 31..38

    $cell = 34;      // width of one tooth cell
    $gap = 2;
    $toothW = $cell - $gap;
    $arch = 16 * $cell;
@endphp

<div class="overflow-x-auto">
    <svg viewBox="0 0 {{ $arch }} 200" class="w-full min-w-[520px]" role="group"
         aria-label="{{ __('app.chart.teeth_title') }}">

        {{-- Quadrant captions --}}
        <text x="{{ $arch * 0.25 }}" y="12" text-anchor="middle" font-size="10" fill="#94a3b8">{{ __('app.chart.upper_right') }}</text>
        <text x="{{ $arch * 0.75 }}" y="12" text-anchor="middle" font-size="10" fill="#94a3b8">{{ __('app.chart.upper_left') }}</text>
        <text x="{{ $arch * 0.25 }}" y="196" text-anchor="middle" font-size="10" fill="#94a3b8">{{ __('app.chart.lower_right') }}</text>
        <text x="{{ $arch * 0.75 }}" y="196" text-anchor="middle" font-size="10" fill="#94a3b8">{{ __('app.chart.lower_left') }}</text>

        {{-- Midline --}}
        <line x1="{{ $arch / 2 }}" y1="18" x2="{{ $arch / 2 }}" y2="182" stroke="#e2e8f0" stroke-dasharray="3 3"/>

        {{-- Upper arch: quadrant 1 (patient's right) then quadrant 2 --}}
        @foreach ($upper as $i => $position)
            @php
                $quadrant = $i < 8 ? 1 : 2;
                $code = $quadrant.$position;
                $x = $i * $cell + $gap / 2;
            @endphp
            <g data-region="{{ $code }}" role="button" tabindex="0"
               aria-label="{{ __('app.chart.teeth_title') }} {{ $code }}">
                <title>{{ $code }}</title>
                <rect class="part" x="{{ $x }}" y="24" width="{{ $toothW }}" height="46" rx="7"
                      fill="#f1f5f9" stroke="#94a3b8"/>
                <text x="{{ $x + $toothW / 2 }}" y="52" text-anchor="middle" font-size="11"
                      font-weight="600" fill="#475569">{{ $code }}</text>
            </g>
        @endforeach

        {{-- Lower arch: quadrant 4 (patient's right) then quadrant 3 --}}
        @foreach ($lower as $i => $position)
            @php
                $quadrant = $i < 8 ? 4 : 3;
                $code = $quadrant.$position;
                $x = $i * $cell + $gap / 2;
            @endphp
            <g data-region="{{ $code }}" role="button" tabindex="0"
               aria-label="{{ __('app.chart.teeth_title') }} {{ $code }}">
                <title>{{ $code }}</title>
                <rect class="part" x="{{ $x }}" y="130" width="{{ $toothW }}" height="46" rx="7"
                      fill="#f1f5f9" stroke="#94a3b8"/>
                <text x="{{ $x + $toothW / 2 }}" y="158" text-anchor="middle" font-size="11"
                      font-weight="600" fill="#475569">{{ $code }}</text>
            </g>
        @endforeach
    </svg>
</div>
