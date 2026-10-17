<?php

namespace Database\Seeders;

use App\Enums\DentalItemLevel;
use App\Models\DentalItem;
use Illuminate\Database\Seeder;

/**
 * The shared dental lab catalog, grouped into the three relationship levels.
 * Each item has a sensible default unit but NO price — labs charge differently,
 * so price is entered per order. clinic_id stays null (shared / global).
 */
class DentalItemsSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            // ── Patient-level: whole-mouth work, no specific tooth ──────────
            DentalItemLevel::Patient->value => [
                ['en' => 'Complete denture', 'ar' => 'طقم أسنان كامل', 'unit' => 'arch'],
                ['en' => 'Partial denture', 'ar' => 'طقم أسنان جزئي', 'unit' => 'arch'],
                ['en' => 'Retainer', 'ar' => 'مثبّت (ريتينر)', 'unit' => 'arch'],
                ['en' => 'Night guard / splint', 'ar' => 'حارس ليلي / جبيرة', 'unit' => 'piece'],
                ['en' => 'Bite registration', 'ar' => 'تسجيل العضة', 'unit' => 'piece'],
            ],

            // ── Tooth-level: needs specific teeth ───────────────────────────
            DentalItemLevel::Tooth->value => [
                ['en' => 'Crown', 'ar' => 'تاج', 'unit' => 'tooth'],
                ['en' => 'Veneer', 'ar' => 'فينير', 'unit' => 'tooth'],
                ['en' => 'Inlay / Onlay', 'ar' => 'حشو داخلي / خارجي', 'unit' => 'tooth'],
                ['en' => 'Post & core', 'ar' => 'دعامة ولب', 'unit' => 'tooth'],
                ['en' => 'Implant crown', 'ar' => 'تاج زرعة', 'unit' => 'tooth'],
                ['en' => 'Bridge', 'ar' => 'جسر', 'unit' => 'unit'],
            ],

            // ── Clinic-general: consumables / materials (tracked in stock) ──
            DentalItemLevel::ClinicGeneral->value => [
                ['en' => 'Impression material', 'ar' => 'مادة الطبعة', 'unit' => 'ml'],
                ['en' => 'Alginate', 'ar' => 'ألجينات', 'unit' => 'gram'],
                ['en' => 'Dental stone / plaster', 'ar' => 'جبس أسنان', 'unit' => 'gram'],
                ['en' => 'Shade guide', 'ar' => 'دليل الألوان', 'unit' => 'piece'],
                ['en' => 'Impression tray', 'ar' => 'حامل الطبعة', 'unit' => 'piece'],
                ['en' => 'Wax', 'ar' => 'شمع', 'unit' => 'gram'],
            ],
        ];

        foreach ($catalog as $level => $items) {
            foreach (array_values($items) as $sort => $item) {
                DentalItem::updateOrCreate(
                    ['clinic_id' => null, 'level' => $level, 'name' => $item['en']],
                    [
                        'name_ar' => $item['ar'],
                        'default_unit' => $item['unit'],
                        'is_active' => true,
                        'sort' => $sort,
                    ],
                );
            }
        }
    }
}
