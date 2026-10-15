{{--
    A realistic Palmer/quadrant dental chart (UR/UL/DR/DL × 1–8). Each jaw is a
    parabolic dental arch (flat-ish anterior, deeper U, molars turning back), with
    teeth spaced evenly by arc length and fanned along the curve's tangent. Crowns
    face the occlusal (biting) side; roots point into the gum. Per-type silhouettes
    (incisor, canine, premolar, molar). Pure SVG + data-tooth hooks, so the page
    script drives selection unchanged.

    Expects: $chartId (unique DOM id).
--}}
@php
    use App\Support\DentalTooth;

    $arches = DentalTooth::arches();
    $upper = array_merge(array_reverse($arches['upper']['UR']), $arches['upper']['UL']); // UR8..UR1, UL1..UL8
    $lower = array_merge(array_reverse($arches['lower']['DR']), $arches['lower']['DL']); // DR8..DR1, DL1..DL8

    $cx = 260;

    // Crown-up anatomical silhouettes (gum line at y=0, crown toward −y, root
    // toward +y): root → crown → cervical line → occlusal grooves.
    $shapes = [
        'incisor' =>
            '<path class="tooth-root" d="M-4.5,0 Q-5,9 -1.8,19 Q0,21 1.8,19 Q5,9 4.5,0 Z"/>'
            .'<path class="tooth-crown" d="M-6,0 L-6,-16 Q-6,-21 -3,-21 Q0,-19 3,-21 Q6,-21 6,-16 L6,0 Z"/>'
            .'<path class="tooth-cervical" d="M-6,0 Q0,2 6,0"/>'
            .'<path class="tooth-groove" d="M0,-5 L0,-16"/>',
        'canine' =>
            '<path class="tooth-root" d="M-4.5,0 Q-5.5,11 -1.8,21 Q0,23 1.8,21 Q5.5,11 4.5,0 Z"/>'
            .'<path class="tooth-crown" d="M-6,0 L-6,-11 Q-6,-14 -3.5,-15.5 L0,-25 L3.5,-15.5 Q6,-14 6,-11 L6,0 Z"/>'
            .'<path class="tooth-cervical" d="M-6,0 Q0,2 6,0"/>'
            .'<path class="tooth-groove" d="M0,-7 L0,-17"/>',
        'premolar' =>
            '<path class="tooth-root" d="M-5,0 Q-5.5,10 -2,19 Q0,21 2,19 Q5.5,10 5,0 Z"/>'
            .'<path class="tooth-crown" d="M-7.5,0 L-7.5,-12 Q-7.5,-17 -4,-18 Q-1.5,-14 0,-15 Q1.5,-14 4,-18 Q7.5,-17 7.5,-12 L7.5,0 Z"/>'
            .'<path class="tooth-cervical" d="M-7.5,0 Q0,2 7.5,0"/>'
            .'<path class="tooth-groove" d="M0,-13 L0,-5"/>',
        'molar' =>
            '<path class="tooth-root" d="M-8,0 Q-10,10 -6.5,19 Q-4.5,20.5 -3.5,18 Q-4.5,9 -3,0 Z"/>'
            .'<path class="tooth-root" d="M8,0 Q10,10 6.5,19 Q4.5,20.5 3.5,18 Q4.5,9 3,0 Z"/>'
            .'<path class="tooth-crown" d="M-10.5,0 L-10.5,-11 Q-10.5,-16 -7,-17 Q-4.5,-13 -2.5,-15 Q0,-17 2.5,-15 Q4.5,-13 7,-17 Q10.5,-16 10.5,-11 L10.5,0 Z"/>'
            .'<path class="tooth-cervical" d="M-10.5,0 Q0,2.5 10.5,0"/>'
            .'<path class="tooth-groove" d="M-7,-9 L7,-9"/>'
            .'<path class="tooth-groove" d="M0,-15 L0,-4"/>',
    ];
    $typeFor = fn (int $pos) => $pos <= 2 ? 'incisor' : ($pos === 3 ? 'canine' : ($pos <= 5 ? 'premolar' : 'molar'));

    // 16 tooth placements along a parabolic arch, even by arc length, each with a
    // rotation so the crown faces the occlusal side. Returns [[x, y, rot], …].
    $archPlacements = function (bool $isUpper) use ($cx) {
        $W = 215;                      // half-width at the molars
        $D = 165;                      // arch depth (front → back)
        $base = $isUpper ? 44 : 406;   // anterior apex y
        $ddir = $isUpper ? 1 : -1;     // depth grows downward (upper) / upward (lower)

        // Slightly sublinear in x so anterior teeth aren't cramped and molars
        // turn back in — a real arch, not a semicircle.
        $xp = fn ($p) => $cx + $W * ($p < 0 ? -pow(-$p, 0.92) : pow($p, 0.92));
        $yp = fn ($p) => $base + $ddir * $D * ($p * $p);

        // Sample the curve, then walk it at equal arc-length steps.
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

            // Tangent from neighbours → inward normal (toward the concave side).
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

    $render = function (string $code, array $xyr) use ($shapes, $typeFor) {
        $pos = (int) substr($code, 2);
        [$x, $y, $r] = $xyr;

        return '<g class="tooth" data-tooth="'.$code.'" transform="translate('.$x.','.$y.') rotate('.$r.')">'
            .'<title>'.$code.'</title>'.$shapes[$typeFor($pos)]
            .'<text class="tooth-mark" data-tooth-sym="'.$code.'" transform="rotate('.(-$r).')" text-anchor="middle" dy="-9"></text>'
            .'<text class="tooth-num" transform="rotate('.(-$r).')" text-anchor="middle" dy="3">'.$pos.'</text></g>';
    };
@endphp

<div class="teeth-chart" id="{{ $chartId }}" data-teeth-chart>
    <svg viewBox="0 0 520 450" class="teeth-svg" role="group" aria-label="{{ __('app.dental.teeth.title') }}">
        <text x="120" y="16" class="teeth-cap" text-anchor="middle">{{ __('app.dental.teeth.upper_right') }}</text>
        <text x="400" y="16" class="teeth-cap" text-anchor="middle">{{ __('app.dental.teeth.upper_left') }}</text>
        <text x="120" y="446" class="teeth-cap" text-anchor="middle">{{ __('app.dental.teeth.lower_right') }}</text>
        <text x="400" y="446" class="teeth-cap" text-anchor="middle">{{ __('app.dental.teeth.lower_left') }}</text>
        <line x1="260" y1="40" x2="260" y2="410" class="teeth-midline"/>

        @foreach($upper as $i => $code){!! $render($code, $upperPos[$i]) !!}@endforeach
        @foreach($lower as $i => $code){!! $render($code, $lowerPos[$i]) !!}@endforeach
    </svg>
</div>

@once
@push('styles')
<style>
    .teeth-chart { max-width: 560px; margin: 0 auto; }
    .teeth-svg { width: 100%; height: auto; display: block; touch-action: manipulation; }
    .teeth-cap { font-size: 11px; fill: #94a3b8; }
    .teeth-midline { stroke: #e2e8f0; stroke-dasharray: 3 4; }
    .tooth { cursor: pointer; }
    .tooth .tooth-crown { fill: #ffffff; stroke: #94a3b8; stroke-width: 1.4; stroke-linejoin: round; transition: fill .12s, stroke .12s; }
    .tooth .tooth-root { fill: #eef1f5; stroke: #cbd5e1; stroke-width: 1; transition: fill .12s; }
    .tooth .tooth-cervical { fill: none; stroke: #cbd5e1; stroke-width: 1; stroke-linecap: round; }
    .tooth .tooth-groove { fill: none; stroke: #cbd5e1; stroke-width: 1; stroke-linecap: round; transition: stroke .12s; }
    .tooth .tooth-num { font-size: 9px; font-weight: 700; fill: #64748b; pointer-events: none; }
    .tooth:hover .tooth-crown { fill: #eef2ff; stroke: #6366f1; }
    .tooth.is-sel .tooth-crown { fill: #4f46e5; stroke: #3730a3; }
    .tooth.is-sel .tooth-root { fill: #a5b4fc; stroke: #818cf8; }
    .tooth.is-sel .tooth-cervical, .tooth.is-sel .tooth-groove { stroke: #c7d2fe; }
    .tooth.is-sel .tooth-num { fill: #ffffff; }

    /* Patient marks overlaid from the examination chart: condition colours the
       crown, treatment shows as a white symbol. Selection still wins visually. */
    .tooth .tooth-mark { font-size: 10px; font-weight: 800; fill: #ffffff; pointer-events: none; }
    .tooth.tooth-marked .tooth-crown { fill: var(--mark); stroke: #64748b; }
    .tooth.tooth-marked:hover .tooth-crown { fill: var(--mark); filter: brightness(0.92); }
    .tooth.tooth-marked.is-sel .tooth-crown { fill: #4f46e5; stroke: #3730a3; filter: none; }
    .tooth.tooth-marked .tooth-num { fill: #ffffff; }
</style>
@endpush
@endonce
