<?php

namespace App\Enums;

/**
 * The work done on a tooth (الأعمال التي تمت): filling, crown, bridge, etc.
 * Shown on the dental chart as a coloured shape/glyph so the doctor can see at a
 * glance what has already been done on each tooth.
 */
enum DentalToothTreatment: string
{
    case Filling = 'filling';
    case Crown = 'crown';
    case Bridge = 'bridge';
    case RootCanal = 'root_canal';
    case Extraction = 'extraction';
    case Implant = 'implant';
    case Veneer = 'veneer';
    case Scaling = 'scaling';

    public function label(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this) {
            self::Filling => $ar ? 'حشو' : 'Filling',
            self::Crown => $ar ? 'تاج' : 'Crown',
            self::Bridge => $ar ? 'جسر / تركيب' : 'Bridge',
            self::RootCanal => $ar ? 'علاج عصب' : 'Root canal',
            self::Extraction => $ar ? 'خلع' : 'Extraction',
            self::Implant => $ar ? 'زرعة' : 'Implant',
            self::Veneer => $ar ? 'فينير' : 'Veneer',
            self::Scaling => $ar ? 'تنظيف' : 'Scaling',
        };
    }

    /** Hex colour for the treatment marker. */
    public function color(): string
    {
        return match ($this) {
            self::Filling => '#3b82f6',
            self::Crown => '#6366f1',
            self::Bridge => '#7c3aed',
            self::RootCanal => '#14b8a6',
            self::Extraction => '#475569',
            self::Implant => '#0891b2',
            self::Veneer => '#d946ef',
            self::Scaling => '#0ea5e9',
        };
    }

    /** Short glyph drawn on the tooth. */
    public function symbol(): string
    {
        return match ($this) {
            self::Filling => '●',
            self::Crown => '♛',
            self::Bridge => '═',
            self::RootCanal => '▲',
            self::Extraction => '✕',
            self::Implant => '⬣',
            self::Veneer => 'V',
            self::Scaling => '≋',
        };
    }

    /**
     * A small SVG marker drawn on the crown (upright, centred near y=-10), the
     * way dentists annotate work on a chart: a gold cap for a crown, a filled
     * box for a filling, a red X for an extraction, screw threads for an implant,
     * and so on. Returned as inner SVG to place inside a counter-rotated group.
     */
    public function marker(): string
    {
        return match ($this) {
            self::Filling => '<rect x="-3" y="-12" width="6" height="6" rx="1" fill="#1e40af"/>',
            self::Crown => '<path d="M-7,-3 Q-7,-18 0,-18 Q7,-18 7,-3" fill="none" stroke="#f59e0b" stroke-width="2.4" stroke-linecap="round"/>',
            self::Bridge => '<rect x="-8" y="-11" width="16" height="3" rx="1.5" fill="#7c3aed"/><circle cx="-6" cy="-9.5" r="1.3" fill="#7c3aed"/><circle cx="6" cy="-9.5" r="1.3" fill="#7c3aed"/>',
            self::RootCanal => '<path d="M-2.6,-13 L2.6,-13 L0,-17 Z" fill="#dc2626"/><path d="M0,-13 L0,-2" stroke="#dc2626" stroke-width="1.6"/>',
            self::Extraction => '<path d="M-6,-15 L6,-4 M6,-15 L-6,-4" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round"/>',
            self::Implant => '<path d="M-3.6,-13 H3.6 M-4,-10.5 H4 M-3.6,-8 H3.6 M-3,-5.5 H3 M-2,-3 H2" fill="none" stroke="#0e7490" stroke-width="1.3"/>',
            self::Veneer => '<rect x="-5.5" y="-17.5" width="11" height="5.5" rx="1" fill="#d946ef" opacity="0.85"/>',
            self::Scaling => '<path d="M-5,-8 q2.5,-3.5 5,0 q2.5,3.5 5,0" fill="none" stroke="#0ea5e9" stroke-width="1.5" stroke-linecap="round"/>',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }
}
