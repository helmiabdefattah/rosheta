<?php

namespace App\Support;

use App\Models\Specialization;

/**
 * Whether a specialisation is women's health / obstetrics & gynaecology, so the
 * examination screen can show the pregnancy follow-up record (dating, fetal
 * data at each visit, a gestational age that is recomputed for today).
 *
 * Matched on the specialisation name — the same approach ClinicalChart and
 * PediatricCare use — with Arabic spellings flattened and English keywords
 * alongside.
 */
class WomensCare
{
    /** Name keywords that mark a clinic as women's / obstetric care. */
    private const KEYWORDS = [
        'نساء', 'توليد', 'نسائيه', 'حمل وولاده', 'عقم',
        'gyn', 'obstet', 'obgyn', 'maternity', 'fertility',
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

    /** Kept in step with ClinicalChart::normalise / PediatricCare::normalise. */
    private static function normalise(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return strtr($value, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا',
            'ى' => 'ي', 'ة' => 'ه', 'ؤ' => 'و', 'ئ' => 'ي',
        ]);
    }
}
