<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\ExpenseCategory;

/**
 * Default expense categories seeded the first time a clinic opens the expenses
 * module. Every clinic gets the common set; dental clinics additionally get
 * lab- and materials-related categories. Seeding is idempotent: it only runs
 * when the clinic has no categories yet.
 */
class ExpenseCategoryPresets
{
    /** Common categories for every clinic type: [name, icon]. */
    private const COMMON = [
        ['Rent', '🏠'],
        ['Utilities', '💡'],
        ['Salaries & Wages', '👥'],
        ['Equipment', '🩻'],
        ['Maintenance', '🔧'],
        ['Supplies & Consumables', '📦'],
        ['Marketing', '📣'],
        ['Software & Subscriptions', '💻'],
        ['Taxes & Fees', '🧾'],
        ['Other', '🗂'],
    ];

    /** Extra categories for dental clinics: [name, icon]. */
    private const DENTAL = [
        ['Dental Lab Fees', '🦷'],
        ['Dental Materials', '🧪'],
        ['Impression Materials', '🦿'],
        ['Lab Transport', '🚚'],
    ];

    /**
     * Seed the preset categories for a clinic if it has none. Returns the number
     * created (0 when categories already existed).
     */
    public static function seedFor(Clinic $clinic, ?Doctor $doctor = null): int
    {
        if ($clinic->expenseCategories()->exists()) {
            return 0;
        }

        $rows = self::COMMON;
        if (self::isDental($doctor)) {
            // Dental extras sit just before the catch-all "Other".
            array_splice($rows, count($rows) - 1, 0, self::DENTAL);
        }

        $order = 0;
        foreach ($rows as [$name, $icon]) {
            ExpenseCategory::create([
                'clinic_id' => $clinic->id,
                'name' => $name,
                'icon' => $icon,
                'is_active' => true,
                'sort_order' => $order++,
            ]);
        }

        return count($rows);
    }

    private static function isDental(?Doctor $doctor): bool
    {
        return $doctor
            && ClinicalChart::forSpecialization($doctor->specialization) === ClinicalChart::TEETH;
    }
}
