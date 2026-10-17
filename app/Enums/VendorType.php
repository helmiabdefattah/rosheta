<?php

namespace App\Enums;

/**
 * What kind of party a vendor is, so expense reports can group spend by the
 * sort of supplier it went to (lab, landlord, utility, …).
 */
enum VendorType: string
{
    case Lab = 'lab';
    case Supplier = 'supplier';
    case Landlord = 'landlord';
    case Utility = 'utility';
    case Staff = 'staff';
    case Government = 'government';
    case Other = 'other';

    public function label(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this) {
            self::Lab => $ar ? 'معمل' : 'Lab',
            self::Supplier => $ar ? 'مورّد' : 'Supplier',
            self::Landlord => $ar ? 'مالك العقار' : 'Landlord',
            self::Utility => $ar ? 'مرافق' : 'Utility',
            self::Staff => $ar ? 'موظف' : 'Staff',
            self::Government => $ar ? 'جهة حكومية' : 'Government',
            self::Other => $ar ? 'أخرى' : 'Other',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }
}
