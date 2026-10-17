<?php

namespace App\Support;

/**
 * Quadrant-based (Palmer-style with letter prefixes) tooth coding for the dental
 * lab module. Format = quadrant prefix + position 1–8, counting from the midline
 * outward:
 *
 *   UR1..UR8 = Upper Right   UL1..UL8 = Upper Left
 *   DR1..DR8 = Lower Right   DL1..DL8 = Lower (Down) Left
 *
 * These codes are what is stored in the database. A map to FDI is provided for
 * reference only — the lab module never stores FDI.
 */
class DentalTooth
{
    /** Quadrant prefix => its arch ('upper' | 'lower') and side. */
    public const QUADRANTS = [
        'UR' => ['arch' => 'upper', 'side' => 'right'],
        'UL' => ['arch' => 'upper', 'side' => 'left'],
        'DR' => ['arch' => 'lower', 'side' => 'right'],
        'DL' => ['arch' => 'lower', 'side' => 'left'],
    ];

    public const POSITIONS = [1, 2, 3, 4, 5, 6, 7, 8];

    /** Palmer quadrant => the FDI quadrant digit it corresponds to. */
    private const FDI_QUADRANT = ['UR' => 1, 'UL' => 2, 'DL' => 3, 'DR' => 4];

    /**
     * All 32 adult-tooth codes.
     *
     * @return array<int, string>
     */
    public static function codes(): array
    {
        $codes = [];
        foreach (array_keys(self::QUADRANTS) as $prefix) {
            foreach (self::POSITIONS as $n) {
                $codes[] = $prefix.$n;
            }
        }

        return $codes;
    }

    public static function isValid(string $code): bool
    {
        return in_array(strtoupper($code), self::codes(), true);
    }

    /**
     * Keep only valid, unique codes from an arbitrary list (defensive cleanup
     * before storing).
     *
     * @param  array<int, mixed>  $codes
     * @return array<int, string>
     */
    public static function sanitize(array $codes): array
    {
        $clean = [];
        foreach ($codes as $code) {
            $code = strtoupper(trim((string) $code));
            if (self::isValid($code) && ! in_array($code, $clean, true)) {
                $clean[] = $code;
            }
        }

        return $clean;
    }

    /** Palmer code for an FDI number, e.g. 11 → UR1, 38 → DL8. */
    public static function fromFdi(string $fdi): ?string
    {
        $fdi = trim($fdi);
        if (strlen($fdi) !== 2 || ! ctype_digit($fdi)) {
            return null;
        }

        $prefix = array_search((int) $fdi[0], self::FDI_QUADRANT, true);
        $position = (int) $fdi[1];

        if ($prefix === false || ! in_array($position, self::POSITIONS, true)) {
            return null;
        }

        return $prefix.$position;
    }

    /** FDI equivalent of a Palmer code, e.g. UR1 → 11, DL8 → 38. */
    public static function toFdi(string $code): ?string
    {
        $code = strtoupper($code);
        $prefix = substr($code, 0, 2);
        $position = substr($code, 2);

        if (! isset(self::FDI_QUADRANT[$prefix]) || ! ctype_digit($position)) {
            return null;
        }

        return self::FDI_QUADRANT[$prefix].$position;
    }

    /**
     * Rows for the chart UI: one entry per quadrant in display order, positions
     * laid out from the midline outward. Upper arch first, then lower; each arch
     * has its right quadrant then left (as the clinician faces the patient).
     *
     * @return array{upper: array<string, array<int, string>>, lower: array<string, array<int, string>>}
     */
    public static function arches(): array
    {
        $build = fn (string $prefix) => array_map(fn (int $n) => $prefix.$n, self::POSITIONS);

        return [
            'upper' => ['UR' => $build('UR'), 'UL' => $build('UL')],
            'lower' => ['DR' => $build('DR'), 'DL' => $build('DL')],
        ];
    }
}
