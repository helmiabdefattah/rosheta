<?php

namespace App\Enums;

/**
 * How an expense payment was settled. Recorded on each expense_payments row so
 * reports can split cash vs. bank vs. card outflow.
 */
enum ExpensePaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case Wallet = 'wallet';
    case Other = 'other';

    public function label(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this) {
            self::Cash => $ar ? 'نقدي' : 'Cash',
            self::Card => $ar ? 'بطاقة' : 'Card',
            self::BankTransfer => $ar ? 'تحويل بنكي' : 'Bank transfer',
            self::Cheque => $ar ? 'شيك' : 'Cheque',
            self::Wallet => $ar ? 'محفظة إلكترونية' : 'E-wallet',
            self::Other => $ar ? 'أخرى' : 'Other',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $m) => $m->value, self::cases());
    }
}
