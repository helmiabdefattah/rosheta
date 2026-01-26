<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SpecializationsSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            'باطنة',
            'باطنة عامة',
            'أطفال',
            'جراحة عامة',
            'جراحة عظام',
            'عظام',
            'نساء وتوليد',
            'قلب وأوعية دموية',
            'مخ وأعصاب',
            'أنف وأذن وحنجرة',
            'عيون',
            'جلدية',
            'نفسية وعصبية',
            'مسالك بولية',
            'صدر',
            'كبد وجهاز هضمي',
            'غدد صماء وسكر',
            'تخسيس وتغذية',
            'أسنان',
            'تقويم أسنان',
            'جراحة فم وأسنان',
            'أشعة',
            'تحاليل طبية',
            'علاج طبيعي',
            'روماتيزم',
            'أورام',
            'جراحة مخ وأعصاب',
            'جراحة قلب وصدر',
            'جراحة تجميل',
            'حساسية ومناعة',
            'أمراض دم',
            'سمعيات',
            'طب أسرة',
            'طب المسنين',
            'طب طوارئ',
        ];

        foreach ($specializations as $index => $name) {
            $slug = Str::slug($name, '-') ?: 'specialization-' . ($index + 1);
            Specialization::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'  => $name,
                    'brief' => 'تخصص ' . $name,
                ]
            );
        }
    }
}
