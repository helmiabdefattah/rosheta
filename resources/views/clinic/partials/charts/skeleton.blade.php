{{--
    A schematic skeleton — clear and clickable rather than anatomically drawn.
    Left and right are the patient's own, so the patient's right sits on the
    viewer's left, as it does on an X-ray hung the conventional way.

    Every region key here must exist in ClinicalChart::regions('skeleton'), which
    is also the allow-list the controller validates against.
--}}
@php
    /** One clickable bone: key, shape, and the geometry for that shape. */
    $bones = [
        ['skull',           'ellipse', ['cx' => 100, 'cy' => 26,  'rx' => 17, 'ry' => 20]],
        ['mandible',        'rect',    ['x' => 90,  'y' => 44,  'w' => 20, 'h' => 8,  'r' => 4]],
        ['cervical-spine',  'rect',    ['x' => 96,  'y' => 54,  'w' => 8,  'h' => 16, 'r' => 3]],

        ['clavicle-right',  'rect',    ['x' => 66,  'y' => 70,  'w' => 28, 'h' => 6,  'r' => 3]],
        ['clavicle-left',   'rect',    ['x' => 106, 'y' => 70,  'w' => 28, 'h' => 6,  'r' => 3]],
        ['shoulder-right',  'circle',  ['cx' => 64, 'cy' => 78,  'r' => 8]],
        ['shoulder-left',   'circle',  ['cx' => 136, 'cy' => 78, 'r' => 8]],

        ['ribs',            'rect',    ['x' => 78,  'y' => 80,  'w' => 44, 'h' => 46, 'r' => 12]],
        ['thoracic-spine',  'rect',    ['x' => 96,  'y' => 80,  'w' => 8,  'h' => 46, 'r' => 3]],
        ['lumbar-spine',    'rect',    ['x' => 96,  'y' => 128, 'w' => 8,  'h' => 22, 'r' => 3]],
        ['pelvis',          'rect',    ['x' => 80,  'y' => 152, 'w' => 40, 'h' => 20, 'r' => 9]],

        ['humerus-right',   'rect',    ['x' => 56,  'y' => 88,  'w' => 9,  'h' => 40, 'r' => 4]],
        ['humerus-left',    'rect',    ['x' => 135, 'y' => 88,  'w' => 9,  'h' => 40, 'r' => 4]],
        ['elbow-right',     'circle',  ['cx' => 60, 'cy' => 132, 'r' => 6]],
        ['elbow-left',      'circle',  ['cx' => 140, 'cy' => 132, 'r' => 6]],
        ['forearm-right',   'rect',    ['x' => 56,  'y' => 138, 'w' => 9,  'h' => 36, 'r' => 4]],
        ['forearm-left',    'rect',    ['x' => 135, 'y' => 138, 'w' => 9,  'h' => 36, 'r' => 4]],
        ['wrist-right',     'circle',  ['cx' => 60, 'cy' => 178, 'r' => 5]],
        ['wrist-left',      'circle',  ['cx' => 140, 'cy' => 178, 'r' => 5]],
        ['hand-right',      'rect',    ['x' => 53,  'y' => 184, 'w' => 14, 'h' => 18, 'r' => 5]],
        ['hand-left',       'rect',    ['x' => 133, 'y' => 184, 'w' => 14, 'h' => 18, 'r' => 5]],

        ['hip-right',       'circle',  ['cx' => 86, 'cy' => 174, 'r' => 7]],
        ['hip-left',        'circle',  ['cx' => 114, 'cy' => 174, 'r' => 7]],
        ['femur-right',     'rect',    ['x' => 81,  'y' => 182, 'w' => 10, 'h' => 44, 'r' => 4]],
        ['femur-left',      'rect',    ['x' => 109, 'y' => 182, 'w' => 10, 'h' => 44, 'r' => 4]],
        ['knee-right',      'circle',  ['cx' => 86, 'cy' => 231, 'r' => 7]],
        ['knee-left',       'circle',  ['cx' => 114, 'cy' => 231, 'r' => 7]],
        ['leg-right',       'rect',    ['x' => 81,  'y' => 238, 'w' => 10, 'h' => 42, 'r' => 4]],
        ['leg-left',        'rect',    ['x' => 109, 'y' => 238, 'w' => 10, 'h' => 42, 'r' => 4]],
        ['ankle-right',     'circle',  ['cx' => 86, 'cy' => 285, 'r' => 5]],
        ['ankle-left',      'circle',  ['cx' => 114, 'cy' => 285, 'r' => 5]],
        ['foot-right',      'rect',    ['x' => 74,  'y' => 291, 'w' => 20, 'h' => 10, 'r' => 4]],
        ['foot-left',       'rect',    ['x' => 106, 'y' => 291, 'w' => 20, 'h' => 10, 'r' => 4]],
    ];

    $labels = \App\Support\ClinicalChart::regions(\App\Support\ClinicalChart::SKELETON);
@endphp

<div class="flex justify-center">
    <svg viewBox="0 0 200 310" class="w-full max-w-[280px]" role="group"
         aria-label="{{ __('app.chart.skeleton_title') }}">
        @foreach ($bones as [$key, $shape, $g])
            <g data-region="{{ $key }}" role="button" tabindex="0" aria-label="{{ $labels[$key] ?? $key }}">
                <title>{{ $labels[$key] ?? $key }}</title>
                @if ($shape === 'rect')
                    <rect class="part" x="{{ $g['x'] }}" y="{{ $g['y'] }}"
                          width="{{ $g['w'] }}" height="{{ $g['h'] }}" rx="{{ $g['r'] }}"
                          fill="#e2e8f0" stroke="#94a3b8" stroke-width="1"/>
                @elseif ($shape === 'circle')
                    <circle class="part" cx="{{ $g['cx'] }}" cy="{{ $g['cy'] }}" r="{{ $g['r'] }}"
                            fill="#e2e8f0" stroke="#94a3b8" stroke-width="1"/>
                @else
                    <ellipse class="part" cx="{{ $g['cx'] }}" cy="{{ $g['cy'] }}"
                             rx="{{ $g['rx'] }}" ry="{{ $g['ry'] }}"
                             fill="#e2e8f0" stroke="#94a3b8" stroke-width="1"/>
                @endif
            </g>
        @endforeach
    </svg>
</div>
