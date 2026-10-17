<?php

namespace App\Enums;

/**
 * An order's payment standing, derived from total paid vs the order total.
 * Not stored — computed on the fly by DentalLabOrder::paymentStatus().
 */
enum DentalPaymentStatus: string
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
            self::Paid => $ar ? 'مدفوع بالكامل' : 'Paid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-red-100 text-red-700',
            self::Partial => 'bg-amber-100 text-amber-800',
            self::Paid => 'bg-emerald-100 text-emerald-800',
        };
    }
}
