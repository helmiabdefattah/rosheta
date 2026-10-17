<?php

namespace App\Enums;

/**
 * The relationship level of a dental catalog item:
 *   - Patient       : whole-mouth work, no specific tooth.
 *   - Tooth         : needs one or more specific teeth.
 *   - ClinicGeneral : consumables/materials, not tied to a patient; these are
 *                     the items tracked in and deducted from clinic stock.
 */
enum DentalItemLevel: string
{
    case Patient = 'patient';
    case Tooth = 'tooth';
    case ClinicGeneral = 'clinic_general';

    public function label(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this) {
            self::Patient => $ar ? 'على مستوى المريض' : 'Patient-level',
            self::Tooth => $ar ? 'على مستوى الأسنان' : 'Tooth-level',
            self::ClinicGeneral => $ar ? 'مستلزمات العيادة' : 'Clinic materials',
        };
    }

    /** Tooth-level items need a teeth selection. */
    public function needsTeeth(): bool
    {
        return $this === self::Tooth;
    }

    /** Clinic-general items are the ones tracked in stock. */
    public function usesStock(): bool
    {
        return $this === self::ClinicGeneral;
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $l) => $l->value, self::cases());
    }
}
