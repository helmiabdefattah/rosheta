<?php

namespace App\Support;

use App\Models\Specialization;

/**
 * Whether a specialisation is paediatric / newborn care, so the examination
 * screen can show the growth-and-vaccination record those doctors keep at every
 * visit: weight, height/length, head circumference, temperature and the
 * vaccinations given.
 *
 * Matched on the specialisation name — the same approach ClinicalChart uses —
 * with Arabic spellings flattened and English keywords alongside, so an entry
 * typed either way still resolves.
 */
class PediatricCare
{
    /** Name keywords that mark a clinic as paediatric / newborn. */
    private const KEYWORDS = [
        'اطفال', 'طفل', 'رضع', 'حديثي الولاده', 'حديث الولاده', 'مواليد',
        'pediatr', 'paediatr', 'neonat', 'newborn', 'infant', 'child',
    ];

    public static function forSpecialization(?Specialization $specialization): bool
    {
        $name = self::normalise((string) ($specialization?->name ?? ''));

        if ($name === '') {
            return false;
        }

        foreach (self::KEYWORDS as $keyword) {
            if (str_contains($name, self::normalise($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Arabic is written with several interchangeable spellings, so both sides of
     * the comparison are flattened before matching (kept in step with
     * ClinicalChart::normalise).
     */
    private static function normalise(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return strtr($value, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا',
            'ى' => 'ي', 'ة' => 'ه', 'ؤ' => 'و', 'ئ' => 'ي',
        ]);
    }
}
