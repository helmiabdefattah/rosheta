<?php

namespace App\Enums;

use Carbon\Carbon;

/**
 * How often a recurring expense repeats. advance() moves a date forward by one
 * period, which the generator uses to roll next_run_on after each firing.
 */
enum RecurrenceInterval: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function label(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this) {
            self::Weekly => $ar ? 'أسبوعي' : 'Weekly',
            self::Monthly => $ar ? 'شهري' : 'Monthly',
            self::Quarterly => $ar ? 'ربع سنوي' : 'Quarterly',
            self::Yearly => $ar ? 'سنوي' : 'Yearly',
        };
    }

    /** Move a date forward by exactly one period of this interval. */
    public function advance(Carbon $date): Carbon
    {
        return match ($this) {
            self::Weekly => $date->copy()->addWeek(),
            self::Monthly => $date->copy()->addMonthNoOverflow(),
            self::Quarterly => $date->copy()->addMonthsNoOverflow(3),
            self::Yearly => $date->copy()->addYearNoOverflow(),
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $i) => $i->value, self::cases());
    }
}
