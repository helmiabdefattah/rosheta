<?php

namespace App\Enums;

/**
 * How much of an expense has been paid: nothing yet (unpaid), some of it
 * (partial), or all of it (paid). Derived from the expense_payments ledger by
 * Expense::recomputeStatus(), so it always matches the recorded payments.
 */
enum ExpenseStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    public function label(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this) {
            self::Unpaid => $ar ? 'غير مدفوع' : 'Unpaid',
            self::Partial => $ar ? 'مدفوع جزئياً' : 'Partially paid',
            self::Paid => $ar ? 'مدفوع' : 'Paid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-red-100 text-red-700',
            self::Partial => 'bg-amber-100 text-amber-800',
            self::Paid => 'bg-emerald-100 text-emerald-700',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
