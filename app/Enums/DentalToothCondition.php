<?php

namespace App\Enums;

/**
 * The clinical condition / problem of a tooth (المشاكل), used to colour the
 * tooth on the dental chart. Each case carries a fill colour and a short glyph
 * so the chart can be read at a glance.
 */
enum DentalToothCondition: string
{
    case Sound = 'sound';
    case Caries = 'caries';
    case Pain = 'pain';
    case Mobility = 'mobility';
    case Fracture = 'fracture';
    case Missing = 'missing';
    case Impacted = 'impacted';
    case Gingivitis = 'gingivitis';

    public function label(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this) {
            self::Sound => $ar ? 'سليم' : 'Sound',
            self::Caries => $ar ? 'تسوس' : 'Caries',
            self::Pain => $ar ? 'ألم' : 'Pain',
            self::Mobility => $ar ? 'حركة' : 'Mobility',
            self::Fracture => $ar ? 'كسر' : 'Fracture',
            self::Missing => $ar ? 'مفقود' : 'Missing',
            self::Impacted => $ar ? 'مطمور' : 'Impacted',
            self::Gingivitis => $ar ? 'التهاب لثة' : 'Gingivitis',
        };
    }

    /** Hex fill colour for the tooth on the chart. */
    public function color(): string
    {
        return match ($this) {
            self::Sound => '#34d399',
            self::Caries => '#ef4444',
            self::Pain => '#f97316',
            self::Mobility => '#f59e0b',
            self::Fracture => '#e11d48',
            self::Missing => '#94a3b8',
            self::Impacted => '#8b5cf6',
            self::Gingivitis => '#ec4899',
        };
    }

    /** Short glyph drawn on the tooth. */
    public function symbol(): string
    {
        return match ($this) {
            self::Sound => '✓',
            self::Caries => 'C',
            self::Pain => '!',
            self::Mobility => 'M',
            self::Fracture => '/',
            self::Missing => '✕',
            self::Impacted => 'I',
            self::Gingivitis => 'G',
        };
    }

    /**
     * Extra SVG marker drawn on the tooth for conditions that read better as a
     * shape than a colour alone — a cavity dot for caries, a crack for a
     * fracture, an X for a missing tooth. Others rely on the crown colour.
     * Returned as inner SVG for a counter-rotated group (upright, around y=-10).
     */
    public function marker(): ?string
    {
        return match ($this) {
            self::Caries => '<circle cx="0" cy="-9" r="2.8" fill="#7c2d12"/>',
            self::Fracture => '<path d="M-3,-17 L1,-11 L-2,-6 L2,-2" fill="none" stroke="#7f1d1d" stroke-width="1.5" stroke-linejoin="round"/>',
            self::Missing => '<path d="M-7,-16 L7,-2 M7,-16 L-7,-2" stroke="#475569" stroke-width="2.2" stroke-linecap="round"/>',
            self::Pain => '<path d="M0,-17 L0,-8 M0,-5.5 L0,-4" stroke="#ea580c" stroke-width="2" stroke-linecap="round"/>',
            self::Mobility => '<path d="M-6,-9 L-9,-9 M6,-9 L9,-9 M-9,-9 L-7,-11 M-9,-9 L-7,-7 M9,-9 L7,-11 M9,-9 L7,-7" fill="none" stroke="#b45309" stroke-width="1.3" stroke-linecap="round"/>',
            default => null,
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
