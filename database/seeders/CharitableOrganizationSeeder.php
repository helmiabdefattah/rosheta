<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\CharitableOrganization;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class CharitableOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some locations
        $cairo = Governorate::where('name', 'Cairo')->first();
        $giza = Governorate::where('name', 'Giza')->first();
        $alexandria = Governorate::where('name', 'Alexandria')->first();
        $sharqia = Governorate::where('name', 'Sharqia')->first();
        $dakahlia = Governorate::where('name', 'Dakahlia')->first();

        if (!$cairo || !$giza || !$alexandria || !$sharqia || !$dakahlia) {
            $this->command->error('Please run EgyptLocationSeeder first to create governorates, cities, and areas.');
            return;
        }

        $created = 0;
        $organizations = [
            [
                'name' => 'جمعية رسالة الخيرية',
                'governorate' => $cairo,
                'city_name' => 'Cairo',
                'address' => 'شارع التحرير، رقم 15، الدور الثالث',
                'phone_numbers' => ['01234567890', '01012345678'],
                'services' => [
                    'تقديم مساعدات غذائية',
                    'توزيع ملابس للفقراء',
                    'مساعدة طبية مجانية',
                    'دعم تعليمي للأطفال'
                ]
            ],
            [
                'name' => 'مؤسسة مصر الخير',
                'governorate' => $giza,
                'city_name' => 'Giza',
                'address' => 'شارع الهرم، عمارة رقم 22',
                'phone_numbers' => ['01123456789', '01234567890', '01098765432'],
                'services' => [
                    'رعاية الأيتام',
                    'مساعدة الأسر المحتاجة',
                    'توفير الأدوية',
                    'برامج تدريب مهني'
                ]
            ],
            [
                'name' => 'جمعية البر والإحسان',
                'governorate' => $alexandria,
                'city_name' => 'Alexandria',
                'address' => 'كورنيش الإسكندرية، مبنى رقم 8',
                'phone_numbers' => ['01234567891'],
                'services' => [
                    'مساعدة مالية شهرية',
                    'توزيع وجبات ساخنة',
                    'دعم صحي للأمهات',
                    'برامج توعية صحية'
                ]
            ],
            [
                'name' => 'مؤسسة الأمل للتنمية',
                'governorate' => $cairo,
                'city_name' => 'Nasr City',
                'address' => 'مدينة نصر، شارع عباس العقاد، رقم 45',
                'phone_numbers' => ['01012345679', '01198765432'],
                'services' => [
                    'دعم تعليمي شامل',
                    'منح دراسية',
                    'توفير كتب وقرطاسية',
                    'دروس تقوية مجانية'
                ]
            ],
            [
                'name' => 'جمعية الرحمة الطبية',
                'governorate' => $giza,
                'city_name' => 'Dokki',
                'address' => 'الدقي، شارع جامعة القاهرة، عمارة 12',
                'phone_numbers' => ['01234567892', '01012345680'],
                'services' => [
                    'عيادات طبية مجانية',
                    'فحوصات دورية',
                    'توفير أدوية',
                    'استشارات طبية'
                ]
            ],
            [
                'name' => 'مؤسسة العطاء الإنساني',
                'governorate' => $sharqia,
                'city_name' => 'Zagazig',
                'address' => 'الزقازيق، شارع الجلاء، رقم 30',
                'phone_numbers' => ['01123456790', '01234567893', '01012345681'],
                'services' => [
                    'مساعدة في حالات الطوارئ',
                    'دعم الأسر المتضررة',
                    'توفير مأوى مؤقت',
                    'مساعدة في الكوارث'
                ]
            ],
            [
                'name' => 'جمعية الخير للرعاية الاجتماعية',
                'governorate' => $dakahlia,
                'city_name' => 'Mansoura',
                'address' => 'المنصورة، شارع الجلاء، مبنى رقم 5',
                'phone_numbers' => ['01234567894'],
                'services' => [
                    'رعاية المسنين',
                    'مساعدة الأرامل',
                    'دعم نفسي واجتماعي',
                    'برامج ترفيهية'
                ]
            ],
            [
                'name' => 'مؤسسة النور التعليمية',
                'governorate' => $cairo,
                'city_name' => 'Heliopolis',
                'address' => 'مصر الجديدة، شارع العروبة، رقم 20',
                'phone_numbers' => ['01012345682', '01123456791'],
                'services' => [
                    'محو الأمية',
                    'دورات تدريبية',
                    'ورش عمل مهنية',
                    'برامج تنمية مهارات'
                ]
            ],
            [
                'name' => 'جمعية الإغاثة السريعة',
                'governorate' => $giza,
                'city_name' => '6th of October City',
                'address' => 'مدينة 6 أكتوبر، الحي الأول، شارع النصر',
                'phone_numbers' => ['01234567895', '01012345683'],
                'services' => [
                    'إغاثة عاجلة',
                    'توزيع بطاطين في الشتاء',
                    'مشاريع مياه شرب',
                    'مساعدة في بناء مساكن'
                ]
            ]
        ];

        foreach ($organizations as $org) {
            if (!$org['governorate']) {
                $this->command->warn("Skipping {$org['name']}: Governorate not found");
                continue;
            }

            $city = City::where('governorate_id', $org['governorate']->id)
                ->where('name', $org['city_name'])
                ->first();

            if (!$city) {
                $this->command->warn("Skipping {$org['name']}: City '{$org['city_name']}' not found in {$org['governorate']->name}");
                continue;
            }

            $area = Area::where('city_id', $city->id)->first();

            if (!$area) {
                $this->command->warn("Skipping {$org['name']}: No area found for city '{$org['city_name']}'");
                continue;
            }

            CharitableOrganization::create([
                'name' => $org['name'],
                'governorate_id' => $org['governorate']->id,
                'city_id' => $city->id,
                'area_id' => $area->id,
                'address' => $org['address'],
                'phone_numbers' => $org['phone_numbers'],
                'services' => $org['services'],
            ]);

            $created++;
            $this->command->info("Created: {$org['name']}");
        }

        $this->command->info("Successfully created {$created} charitable organizations.");
    }
}
