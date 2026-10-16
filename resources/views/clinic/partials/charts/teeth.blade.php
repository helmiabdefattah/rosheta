{{--
    Adult dentition as a realistic parabolic dental arch (anatomical crowns +
    roots), the same drawing the dental-lab module uses, so the examination chart
    shows teeth as they actually look. Keyed by FDI in `data-region` to stay
    compatible with the clinical-chart machinery: the crown carries `.part` (so
    notes / selection / condition colour fill it) and each tooth has a
    `[data-tooth-symbol]` slot the script stamps the treatment glyph into.
--}}
@php
    use App\Support\DentalTooth;

    $arches = DentalTooth::arches();
    $upper = array_merge(array_reverse($arches['upper']['UR']), $arches['upper']['UL']); // UR8..UR1, UL1..UL8
    $lower = array_merge(array_reverse($arches['lower']['DR']), $arches['lower']['DL']); // DR8..DR1, DL1..DL8

    $cx = 260;

    // Crown-up anatomical silhouettes. The crown path carries class `part` so the
    // clinical-chart CSS (hover / has-notes / selected / marked) colours it.
    $shapes = [
        'incisor' =>
            '<path class="tooth-root" d="M-4.5,0 Q-5,9 -1.8,19 Q0,21 1.8,19 Q5,9 4.5,0 Z"/>'
            .'<path class="part" d="M-6,0 L-6,-16 Q-6,-21 -3,-21 Q0,-19 3,-21 Q6,-21 6,-16 L6,0 Z"/>'
            .'<path class="tooth-cervical" d="M-6,0 Q0,2 6,0"/>'
            .'<path class="tooth-groove" d="M0,-5 L0,-16"/>',
        'canine' =>
            '<path class="tooth-root" d="M-4.5,0 Q-5.5,11 -1.8,21 Q0,23 1.8,21 Q5.5,11 4.5,0 Z"/>'
            .'<path class="part" d="M-6,0 L-6,-11 Q-6,-14 -3.5,-15.5 L0,-25 L3.5,-15.5 Q6,-14 6,-11 L6,0 Z"/>'
            .'<path class="tooth-cervical" d="M-6,0 Q0,2 6,0"/>'
            .'<path class="tooth-groove" d="M0,-7 L0,-17"/>',
        'premolar' =>
            '<path class="tooth-root" d="M-5,0 Q-5.5,10 -2,19 Q0,21 2,19 Q5.5,10 5,0 Z"/>'
            .'<path class="part" d="M-7.5,0 L-7.5,-12 Q-7.5,-17 -4,-18 Q-1.5,-14 0,-15 Q1.5,-14 4,-18 Q7.5,-17 7.5,-12 L7.5,0 Z"/>'
            .'<path class="tooth-cervical" d="M-7.5,0 Q0,2 7.5,0"/>'
            .'<path class="tooth-groove" d="M0,-13 L0,-5"/>',
        'molar' =>
            '<path class="tooth-root" d="M-8,0 Q-10,10 -6.5,19 Q-4.5,20.5 -3.5,18 Q-4.5,9 -3,0 Z"/>'
            .'<path class="tooth-root" d="M8,0 Q10,10 6.5,19 Q4.5,20.5 3.5,18 Q4.5,9 3,0 Z"/>'
            .'<path class="part" d="M-10.5,0 L-10.5,-11 Q-10.5,-16 -7,-17 Q-4.5,-13 -2.5,-15 Q0,-17 2.5,-15 Q4.5,-13 7,-17 Q10.5,-16 10.5,-11 L10.5,0 Z"/>'
            .'<path class="tooth-cervical" d="M-10.5,0 Q0,2.5 10.5,0"/>'
            .'<path class="tooth-groove" d="M-7,-9 L7,-9"/>'
            .'<path class="tooth-groove" d="M0,-15 L0,-4"/>',
    ];
    $typeFor = fn (int $pos) => $pos <= 2 ? 'incisor' : ($pos === 3 ? 'canine' : ($pos <= 5 ? 'premolar' : 'molar'));

    // 16 placements along a parabolic arch, even by arc length, each rotated so
    // the crown faces the occlusal side. Returns [[x, y, rot], …].
    $archPlacements = function (bool $isUpper) use ($cx) {
        $W = 215;
        $D = 165;
        $base = $isUpper ? 44 : 406;
        $ddir = $isUpper ? 1 : -1;

        $xp = fn ($p) => $cx + $W * ($p < 0 ? -pow(-$p, 0.92) : pow($p, 0.92));
        $yp = fn ($p) => $base + $ddir * $D * ($p * $p);

        $N = 600;
        $px = [];
        $py = [];
        $cum = [0.0];
        for ($k = 0; $k <= $N; $k++) {
            $p = -1 + 2 * $k / $N;
            $px[$k] = $xp($p);
            $py[$k] = $yp($p);
            if ($k > 0) {
                $dx = $px[$k] - $px[$k - 1];
                $dy = $py[$k] - $py[$k - 1];
                $cum[$k] = $cum[$k - 1] + sqrt($dx * $dx + $dy * $dy);
            }
        }
        $total = $cum[$N];

        $out = [];
        for ($j = 0; $j < 16; $j++) {
            $target = $total * $j / 15;
            $k = 1;
            while ($k < $N && $cum[$k] < $target) {
                $k++;
            }
            $x = $px[$k];
            $y = $py[$k];

            $tx = $px[min($k + 1, $N)] - $px[max($k - 1, 0)];
            $ty = $py[min($k + 1, $N)] - $py[max($k - 1, 0)];
            $nx = -$ty;
            $ny = $tx;
            if (($isUpper && $ny < 0) || (! $isUpper && $ny > 0)) {
                $nx = -$nx;
                $ny = -$ny;
            }
            $rot = rad2deg(atan2($ny, $nx)) + 90;

            $out[] = [round($x, 1), round($y, 1), round($rot, 1)];
        }

        return $out;
    };

    $upperPos = $archPlacements(true);
    $lowerPos = $archPlacements(false);

    // Recorded marks for this patient, keyed by FDI (from the clinical chart).
    $marks = $toothMarks ?? [];

    // Render one tooth keyed by its FDI code (the chart notes' region), not Palmer.
    // Any recorded condition/treatment is drawn as a realistic, upright marker on
    // the crown (counter-rotated so it stays readable whatever the tooth's angle).
    $render = function (string $code, array $xyr) use ($shapes, $typeFor, $marks) {
        $pos = (int) substr($code, 2);
        $fdi = DentalTooth::toFdi($code) ?? $code;
        [$x, $y, $r] = $xyr;

        // Markers are drawn in the tooth's own frame (crown toward −y), so they sit
        // on the crown and follow the tooth's angle — no counter-rotation.
        $overlay = '';
        if (isset($marks[$fdi])) {
            $inner = ($marks[$fdi]['condition']?->marker() ?? '').($marks[$fdi]['treatment']?->marker() ?? '');
            if ($inner !== '') {
                $overlay = '<g class="tooth-overlay">'.$inner.'</g>';
            }
        }

        // Labelled by quadrant code (UR/UL/DR/DL + 1–8), e.g. UL1, not FDI —
        // but still keyed on FDI in data-region for the clinical-chart machinery.
        return '<g class="tooth" data-region="'.$fdi.'" role="button" tabindex="0" aria-label="'.$code.'" transform="translate('.$x.','.$y.') rotate('.$r.')">'
            .'<title>'.$code.'</title>'.$shapes[$typeFor($pos)].$overlay
            .'<text class="tooth-code" transform="rotate('.(-$r).')" text-anchor="middle" dy="3">'.$code.'</text></g>';
    };
@endphp

<div class="teeth-anat overflow-x-auto">
    <svg viewBox="0 0 520 450" class="teeth-svg w-full min-w-[460px]" role="group" aria-label="{{ __('app.chart.teeth_title') }}">
        <text x="120" y="16" class="teeth-cap" text-anchor="middle">{{ __('app.chart.upper_right') }}</text>
        <text x="400" y="16" class="teeth-cap" text-anchor="middle">{{ __('app.chart.upper_left') }}</text>
        <text x="120" y="446" class="teeth-cap" text-anchor="middle">{{ __('app.chart.lower_right') }}</text>
        <text x="400" y="446" class="teeth-cap" text-anchor="middle">{{ __('app.chart.lower_left') }}</text>
        <line x1="260" y1="40" x2="260" y2="410" class="teeth-midline"/>

        @foreach($upper as $i => $code){!! $render($code, $upperPos[$i]) !!}@endforeach
        @foreach($lower as $i => $code){!! $render($code, $lowerPos[$i]) !!}@endforeach
    </svg>
</div>

@once
@push('styles')
<style>
    .teeth-anat { width: 100%; margin: 0 auto; }
    .teeth-anat .teeth-svg { width: 100%; height: auto; display: block; touch-action: manipulation; }
    .teeth-anat .teeth-cap { font-size: 11px; fill: #94a3b8; }
    .teeth-anat .teeth-midline { stroke: #e2e8f0; stroke-dasharray: 3 4; }
    .teeth-anat .tooth { cursor: pointer; }
    /* Base tooth look; the clinical-chart rules recolour `.part` on state. */
    .teeth-anat .part { fill: #ffffff; stroke: #94a3b8; stroke-width: 1.4; stroke-linejoin: round; transition: fill .12s, stroke .12s; }
    .teeth-anat .tooth-root { fill: #eef1f5; stroke: #cbd5e1; stroke-width: 1; transition: fill .12s; }
    .teeth-anat .tooth-cervical { fill: none; stroke: #cbd5e1; stroke-width: 1; stroke-linecap: round; }
    .teeth-anat .tooth-groove { fill: none; stroke: #cbd5e1; stroke-width: 1; stroke-linecap: round; }
    .teeth-anat .tooth-code { font-size: 8px; font-weight: 700; fill: #64748b; pointer-events: none; }
    .teeth-anat .tooth-overlay { pointer-events: none; }
    .teeth-anat .tooth.is-selected .tooth-root { fill: #a5b4fc; stroke: #818cf8; }
</style>
@endpush
@endonce
