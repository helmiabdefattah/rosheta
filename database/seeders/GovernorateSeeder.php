<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            ['name' => 'Cairo', 'name_ar' => 'القاهرة', 'sort_order' => 1],
            ['name' => 'Giza', 'name_ar' => 'الجيزة', 'sort_order' => 2],
            ['name' => 'Alexandria', 'name_ar' => 'الإسكندرية', 'sort_order' => 3],
            ['name' => 'Qalyubia', 'name_ar' => 'القليوبية', 'sort_order' => 4],
            ['name' => 'Dakahlia', 'name_ar' => 'الدقهلية', 'sort_order' => 5],
            ['name' => 'Sharqia', 'name_ar' => 'الشرقية', 'sort_order' => 6],
            ['name' => 'Gharbia', 'name_ar' => 'الغربية', 'sort_order' => 7],
            ['name' => 'Kafr El Sheikh', 'name_ar' => 'كفر الشيخ', 'sort_order' => 8],
            ['name' => 'Monufia', 'name_ar' => 'المنوفية', 'sort_order' => 9],
            ['name' => 'Beheira', 'name_ar' => 'البحيرة', 'sort_order' => 10],
            ['name' => 'Ismailia', 'name_ar' => 'الإسماعيلية', 'sort_order' => 11],
            ['name' => 'Port Said', 'name_ar' => 'بورسعيد', 'sort_order' => 12],
            ['name' => 'Suez', 'name_ar' => 'السويس', 'sort_order' => 13],
            ['name' => 'North Sinai', 'name_ar' => 'شمال سيناء', 'sort_order' => 14],
            ['name' => 'South Sinai', 'name_ar' => 'جنوب سيناء', 'sort_order' => 15],
            ['name' => 'Damietta', 'name_ar' => 'دمياط', 'sort_order' => 16],
            ['name' => 'Beni Suef', 'name_ar' => 'بني سويف', 'sort_order' => 17],
            ['name' => 'Faiyum', 'name_ar' => 'الفيوم', 'sort_order' => 18],
            ['name' => 'Minya', 'name_ar' => 'المنيا', 'sort_order' => 19],
            ['name' => 'Asyut', 'name_ar' => 'أسيوط', 'sort_order' => 20],
            ['name' => 'Sohag', 'name_ar' => 'سوهاج', 'sort_order' => 21],
            ['name' => 'Qena', 'name_ar' => 'قنا', 'sort_order' => 22],
            ['name' => 'Luxor', 'name_ar' => 'الأقصر', 'sort_order' => 23],
            ['name' => 'Aswan', 'name_ar' => 'أسوان', 'sort_order' => 24],
            ['name' => 'Red Sea', 'name_ar' => 'البحر الأحمر', 'sort_order' => 25],
            ['name' => 'New Valley', 'name_ar' => 'الوادي الجديد', 'sort_order' => 26],
            ['name' => 'Matruh', 'name_ar' => 'مطروح', 'sort_order' => 27],
        ];

        foreach ($governorates as $governorate) {
            Governorate::create($governorate);
        }
    }
}
