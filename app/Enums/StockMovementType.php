<?php

namespace App\Enums;

/**
 * Why a stock quantity changed: stock coming In, going Out (consumed), or a
 * manual Adjustment/correction. The movement's signed quantity carries the
 * direction; this records the intent.
 */
enum StockMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjust = 'adjust';

    public function label(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this) {
            self::In => $ar ? 'إضافة' : 'Stock in',
            self::Out => $ar ? 'صرف' : 'Stock out',
            self::Adjust => $ar ? 'تسوية' : 'Adjustment',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::In => 'bg-emerald-100 text-emerald-800',
            self::Out => 'bg-red-100 text-red-700',
            self::Adjust => 'bg-slate-100 text-slate-700',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }
}
