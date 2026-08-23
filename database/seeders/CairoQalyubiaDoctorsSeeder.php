<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Governorate;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Realistic sample doctors + clinics across Cairo and Qalyubia, ready to book.
 *
 * These are plausible, fictional practices (not real individuals) placed in real
 * cities/areas so the search, booking, and clinic-workspace flows can be tested
 * with lifelike data. Each doctor gets a login, a specialization, a clinic with
 * a real location + map pin, weekly hours (Sat–Thu 9–17, closed Friday), and
 * default prices.
 *
 * Run standalone:  php artisan db:seed --class=CairoQalyubiaDoctorsSeeder
 * (It also runs as part of `php artisan db:seed`.)
 *
 * Idempotent: doctors are matched by slug, clinics by (doctor, name), accounts
 * by email — safe to re-run. Every account uses the password: password
 */
class CairoQalyubiaDoctorsSeeder extends Seeder
{
    private const PASSWORD = 'password';

    /** Approximate map coordinates per city, so clinics show on the map. */
    private const CITY_COORDS = [
        'Nasr City' => [30.0566, 31.3300],
        'Maadi' => [29.9603, 31.2569],
        'Heliopolis' => [30.0880, 31.3220],
        'New Cairo' => [30.0300, 31.4700],
        'Shubra' => [30.1100, 31.2440],
        'Helwan' => [29.8500, 31.3340],
        'Banha' => [30.4600, 31.1840],
        'Shubra El-Kheima' => [30.1286, 31.2440],
        'Qalyub' => [30.1790, 31.2060],
        'Obour City' => [30.2280, 31.4700],
    ];

    public function run(): void
    {
        foreach ($this->doctors() as $i => $d) {
            $num = $i + 1;

            $user = User::updateOrCreate(
                ['email' => 'dr'.$num.'@clinics.test'],
                [
                    'name' => $d['name'],
                    'phone_number' => sprintf('010100000%02d', $num),
                    'password' => Hash::make(self::PASSWORD),
                    'is_active' => true,
                ]
            );

            $specialization = $this->specialization($d['specialization']);

            $doctor = Doctor::updateOrCreate(
                ['slug' => 'cq-doctor-'.$num],
                [
                    'name' => $d['name'],
                    'brief' => $d['brief'],
                    'specialization_id' => $specialization->id,
                    'user_id' => $user->id,
                ]
            );

            [$governorate, $city] = $this->location($d['governorate'], $d['city']);
            [$lat, $lng] = self::CITY_COORDS[$d['city']] ?? [null, null];

            $clinic = Clinic::updateOrCreate(
                ['doctor_id' => $doctor->id, 'name' => $d['clinic']],
                [
                    'user_id' => $user->id,
                    'address' => $d['address'],
                    'phone_number' => $d['phone'],
                    'governorate_id' => $governorate->id,
                    'city_id' => $city->id,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'medical_examination_price' => $d['exam'],
                    'follow_up_price' => $d['follow'],
                    'appointment_duration' => 30,
                    'display_show_next_button' => true,
                    'printer_language' => 'ar',
                ]
            );

            $this->workingHours($clinic);
        }

        $this->command->info('Seeded '.count($this->doctors()).' Cairo/Qalyubia doctors + clinics (password: password).');
    }

    /** Sat–Thu 09:00–17:00, closed Friday; mirror into the opening_hours JSON. */
    private function workingHours(Clinic $clinic): void
    {
        foreach (Clinic::DAYS as $day) {
            $closed = $day === 'friday';
            $clinic->workingHours()->updateOrCreate(
                ['day' => $day],
                [
                    'from' => $closed ? null : '09:00',
                    'to' => $closed ? null : '17:00',
                    'is_closed' => $closed,
                ]
            );
        }
        $clinic->syncOpeningHoursFromWorkingHours();
    }

    /** Find a specialization by its Arabic name, creating it if missing. */
    private function specialization(string $name): Specialization
    {
        return Specialization::firstOrCreate(
            ['name' => $name],
            ['slug' => 'spec-'.md5($name), 'brief' => 'تخصص '.$name],
        );
    }

    /**
     * Resolve (or create) the governorate + city so the seeder is self-contained
     * even if the Egypt location seeder hasn't been run.
     *
     * @return array{0: Governorate, 1: City}
     */
    private function location(string $govEn, string $cityEn): array
    {
        $govAr = ['Cairo' => 'القاهرة', 'Qalyubia' => 'القليوبية'][$govEn] ?? $govEn;
        $governorate = Governorate::firstOrCreate(['name' => $govEn], ['name_ar' => $govAr]);

        $cityAr = $this->cityNameAr($cityEn);
        $city = City::firstOrCreate(
            ['governorate_id' => $governorate->id, 'name' => $cityEn],
            ['name_ar' => $cityAr],
        );

        return [$governorate, $city];
    }

    private function cityNameAr(string $cityEn): string
    {
        return [
            'Nasr City' => 'مدينة نصر',
            'Maadi' => 'المعادي',
            'Heliopolis' => 'مصر الجديدة',
            'New Cairo' => 'القاهرة الجديدة',
            'Shubra' => 'شبرا',
            'Helwan' => 'حلوان',
            'Banha' => 'بنها',
            'Shubra El-Kheima' => 'شبرا الخيمة',
            'Qalyub' => 'قليوب',
            'Obour City' => 'مدينة العبور',
        ][$cityEn] ?? $cityEn;
    }

    /** The sample practices. */
    private function doctors(): array
    {
        return [
            // ---- Cairo ----
            [
                'name' => 'د. أحمد سمير عبد العزيز', 'specialization' => 'أطفال',
                'brief' => 'استشاري طب الأطفال وحديثي الولادة، متابعة النمو والتطعيمات.',
                'clinic' => 'عيادة الطفل السليم', 'governorate' => 'Cairo', 'city' => 'Nasr City',
                'address' => 'عمارة 24، شارع مصطفى النحاس، مدينة نصر', 'phone' => '0226700123',
                'exam' => 200, 'follow' => 100,
            ],
            [
                'name' => 'د. منى إبراهيم فؤاد', 'specialization' => 'نساء وتوليد',
                'brief' => 'استشارية النساء والتوليد، متابعة الحمل والولادة والحقن المجهري.',
                'clinic' => 'عيادة المنى للنساء والتوليد', 'governorate' => 'Cairo', 'city' => 'Maadi',
                'address' => '18 شارع 9، المعادي', 'phone' => '0227501234',
                'exam' => 300, 'follow' => 150,
            ],
            [
                'name' => 'د. خالد حسن الشناوي', 'specialization' => 'قلب وأوعية دموية',
                'brief' => 'استشاري القلب والأوعية الدموية، القسطرة وفحوصات الجهد.',
                'clinic' => 'مركز القلب التخصصي', 'governorate' => 'Cairo', 'city' => 'Heliopolis',
                'address' => '45 شارع الحجاز، مصر الجديدة', 'phone' => '0224145678',
                'exam' => 300, 'follow' => 150,
            ],
            [
                'name' => 'د. سارة محمود عطية', 'specialization' => 'جلدية',
                'brief' => 'استشارية الأمراض الجلدية والتجميل والليزر.',
                'clinic' => 'عيادة سارة للجلدية والتجميل', 'governorate' => 'Cairo', 'city' => 'New Cairo',
                'address' => 'التجمع الخامس، شارع التسعين الشمالي', 'phone' => '0261234567',
                'exam' => 250, 'follow' => 125,
            ],
            [
                'name' => 'د. عمرو فتحي الديب', 'specialization' => 'عظام',
                'brief' => 'استشاري جراحة العظام والمفاصل وإصابات الملاعب.',
                'clinic' => 'عيادة العظام والمفاصل', 'governorate' => 'Cairo', 'city' => 'Nasr City',
                'address' => '12 شارع عباس العقاد، مدينة نصر', 'phone' => '0226709876',
                'exam' => 250, 'follow' => 120,
            ],
            [
                'name' => 'د. هالة عادل رشدي', 'specialization' => 'باطنة عامة',
                'brief' => 'استشارية الباطنة العامة والسكر والضغط.',
                'clinic' => 'عيادة الروشتة للباطنة', 'governorate' => 'Cairo', 'city' => 'Shubra',
                'address' => '30 شارع شبرا الرئيسي', 'phone' => '0224201122',
                'exam' => 150, 'follow' => 75,
            ],
            [
                'name' => 'د. طارق عبد الله منصور', 'specialization' => 'أنف وأذن وحنجرة',
                'brief' => 'استشاري الأنف والأذن والحنجرة، جراحات الجيوب واللوز.',
                'clinic' => 'عيادة الأنف والأذن التخصصية', 'governorate' => 'Cairo', 'city' => 'Heliopolis',
                'address' => '8 شارع العروبة، مصر الجديدة', 'phone' => '0224156789',
                'exam' => 200, 'follow' => 100,
            ],
            [
                'name' => 'د. نيرة وائل صادق', 'specialization' => 'أسنان',
                'brief' => 'أخصائية طب وتجميل الأسنان والتركيبات.',
                'clinic' => 'عيادة الابتسامة لطب الأسنان', 'governorate' => 'Cairo', 'city' => 'Maadi',
                'address' => '22 شارع 231، دجلة، المعادي', 'phone' => '0227559988',
                'exam' => 180, 'follow' => 90,
            ],
            [
                'name' => 'د. محمد رأفت زكي', 'specialization' => 'مخ وأعصاب',
                'brief' => 'استشاري المخ والأعصاب، الصداع والصرع واعتلال الأعصاب.',
                'clinic' => 'عيادة المخ والأعصاب', 'governorate' => 'Cairo', 'city' => 'Helwan',
                'address' => '5 شارع الجامعة، حلوان', 'phone' => '0225551234',
                'exam' => 250, 'follow' => 125,
            ],

            // ---- Qalyubia ----
            [
                'name' => 'د. إيمان مصطفى الجندي', 'specialization' => 'أطفال',
                'brief' => 'استشارية طب الأطفال، الرضاعة والتغذية والتطعيمات.',
                'clinic' => 'عيادة الأمل للأطفال', 'governorate' => 'Qalyubia', 'city' => 'Banha',
                'address' => 'شارع فريد ندا، بنها', 'phone' => '0133234455',
                'exam' => 150, 'follow' => 75,
            ],
            [
                'name' => 'د. حسام الدين عبد الحميد', 'specialization' => 'كبد وجهاز هضمي',
                'brief' => 'استشاري الكبد والجهاز الهضمي والمناظير.',
                'clinic' => 'عيادة الجهاز الهضمي', 'governorate' => 'Qalyubia', 'city' => 'Shubra El-Kheima',
                'address' => 'شارع مسطرد الرئيسي، شبرا الخيمة', 'phone' => '0224310099',
                'exam' => 200, 'follow' => 100,
            ],
            [
                'name' => 'د. رشا سعيد عامر', 'specialization' => 'نساء وتوليد',
                'brief' => 'استشارية النساء والولادة والكشف الدوري.',
                'clinic' => 'عيادة رشا للنساء والولادة', 'governorate' => 'Qalyubia', 'city' => 'Qalyub',
                'address' => 'شارع المحطة، قليوب', 'phone' => '0133456677',
                'exam' => 180, 'follow' => 90,
            ],
            [
                'name' => 'د. أيمن كمال الشريف', 'specialization' => 'عظام',
                'brief' => 'استشاري جراحة العظام والإصابات وتغيير المفاصل.',
                'clinic' => 'مركز العظام والإصابات', 'governorate' => 'Qalyubia', 'city' => 'Obour City',
                'address' => 'الحي الأول، مدينة العبور', 'phone' => '0246100200',
                'exam' => 250, 'follow' => 120,
            ],
            [
                'name' => 'د. دعاء ناصر حلمي', 'specialization' => 'جلدية',
                'brief' => 'أخصائية الجلدية والتجميل وعلاج الحساسية.',
                'clinic' => 'عيادة النور للجلدية', 'governorate' => 'Qalyubia', 'city' => 'Banha',
                'address' => 'شارع الجلاء، بنها', 'phone' => '0133678899',
                'exam' => 150, 'follow' => 75,
            ],
        ];
    }
}
