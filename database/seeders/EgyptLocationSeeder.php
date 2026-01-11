<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EgyptLocationSeeder extends Seeder
{
    /**
     * Run the database seeds with clean Egyptian location data.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Area::truncate();
        City::truncate();
        Governorate::truncate();
        Schema::enableForeignKeyConstraints();

        $locations = [
            'Alexandria' => ['name_ar' => 'الإسكندرية', 'cities' => [
                'Alexandria' => 'الإسكندرية', 'Borg El Arab' => 'برج العرب', 'Abu Qir' => 'أبو قير', 'Agami' => 'العجمي', 'Sidi Bishr' => 'سيدي بشر'
            ]],
            'Cairo' => ['name_ar' => 'القاهرة', 'cities' => [
                'Cairo' => 'القاهرة', 'New Cairo' => 'القاهرة الجديدة', 'Heliopolis' => 'مصر الجديدة', 'Nasr City' => 'مدينة نصر', 'Maadi' => 'المعادي', 'Helwan' => 'حلوان', 'Shubra' => 'شبرا', 'Badr City' => 'مدينة بدر', 'Shorouk City' => 'مدينة الشروق', 'Madinaty' => 'مدينتي'
            ]],
            'Giza' => ['name_ar' => 'الجيزة', 'cities' => [
                'Giza' => 'الجيزة', '6th of October City' => 'مدينة 6 أكتوبر', 'Sheikh Zayed City' => 'مدينة الشيخ زايد', 'Haram' => 'الهرم', 'Faisal' => 'فيصل', 'Dokki' => 'الدقي', 'Mohandessin' => 'المهندسين', 'Imbaba' => 'إمبابة'
            ]],
            'Port Said' => ['name_ar' => 'بورسعيد', 'cities' => [
                'Port Said' => 'بورسعيد', 'Port Fouad' => 'بور فؤاد'
            ]],
            'Suez' => ['name_ar' => 'السويس', 'cities' => [
                'Suez' => 'السويس', 'Ain Sukhna' => 'العين السخنة'
            ]],
            'Ismailia' => ['name_ar' => 'الإسماعيلية', 'cities' => [
                'Ismailia' => 'الإسماعيلية', 'Fayid' => 'فايد', 'El Qantara' => 'القنطرة'
            ]],
            'Dakahlia' => ['name_ar' => 'الدقهلية', 'cities' => [
                'Mansoura' => 'المنصورة', 'Talkha' => 'طلخا', 'Mit Ghamr' => 'ميت غمر', 'Dekernes' => 'دكرنس', 'Gamasa' => 'جمصة'
            ]],
            'Sharqia' => ['name_ar' => 'الشرقية', 'cities' => [
                'Zagazig' => 'الزقازيق', '10th of Ramadan' => 'العاشر من رمضان', 'Bilbeis' => 'بلبيس', 'Minya El Qamh' => 'منيا القمح', 'Faqous' => 'فاقوس'
            ]],
            'Gharbia' => ['name_ar' => 'الغربية', 'cities' => [
                'Tanta' => 'طنطا', 'Mahalla al-Kubra' => 'المحلة الكبرى', 'Zefta' => 'زفتى', 'Kafr El-Zayat' => 'كفر الزيات'
            ]],
            'Kafr El-Sheikh' => ['name_ar' => 'كفر الشيخ', 'cities' => [
                'Kafr El-Sheikh' => 'كفر الشيخ', 'Desouk' => 'دسوق', 'Baltim' => 'بلطيم'
            ]],
            'Monufia' => ['name_ar' => 'المنوفية', 'cities' => [
                'Shibin El Kom' => 'شبين الكوم', 'Menouf' => 'منوف', 'Ashmoun' => 'أشمون', 'Quwaysna' => 'قويسنا'
            ]],
            'Beheira' => ['name_ar' => 'البحيرة', 'cities' => [
                'Damanhur' => 'دمنهور', 'Kafr El-Dawwar' => 'كفر الدوار', 'Edko' => 'إدكو', 'Rashid' => 'رشيد'
            ]],
            'Damietta' => ['name_ar' => 'دمياط', 'cities' => [
                'Damietta' => 'دمياط', 'Ras El Bar' => 'رأس البر', 'New Damietta' => 'دمياط الجديدة'
            ]],
            'Qalyubia' => ['name_ar' => 'القليوبية', 'cities' => [
                'Banha' => 'بنها', 'Shubra El-Kheima' => 'شبرا الخيمة', 'Obour City' => 'مدينة العبور', 'Qalyub' => 'قليوب'
            ]],
            'Faiyum' => ['name_ar' => 'الفيوم', 'cities' => [
                'Faiyum' => 'الفيوم', 'Tamiya' => 'طامية', 'Itsa' => 'إطسا'
            ]],
            'Beni Suef' => ['name_ar' => 'بني سويف', 'cities' => [
                'Beni Suef' => 'بني سويف', 'Biba' => 'ببا', 'Nasser' => 'ناصر'
            ]],
            'Minya' => ['name_ar' => 'المنيا', 'cities' => [
                'Minya' => 'المنيا', 'Mallawi' => 'ملوي', 'Maghagha' => 'مغاغة', 'Beni Mazar' => 'بني مزار'
            ]],
            'Asyut' => ['name_ar' => 'أسيوط', 'cities' => [
                'Asyut' => 'أسيوط', 'Dairut' => 'ديروط', 'Manfalut' => 'منفلوط'
            ]],
            'Sohag' => ['name_ar' => 'سوهاج', 'cities' => [
                'Sohag' => 'سوهاج', 'Akhmim' => 'أخميم', 'Tahta' => 'طهطا', 'Girga' => 'جرجا'
            ]],
            'Qena' => ['name_ar' => 'قنا', 'cities' => [
                'Qena' => 'قنا', 'Nag Hammadi' => 'نجع حمادي', 'Qus' => 'قوص'
            ]],
            'Luxor' => ['name_ar' => 'الأقصر', 'cities' => [
                'Luxor' => 'الأقصر', 'Esna' => 'إسنا'
            ]],
            'Aswan' => ['name_ar' => 'أسوان', 'cities' => [
                'Aswan' => 'أسوان', 'Kom Ombo' => 'كوم أمبو', 'Edfu' => 'إدفو'
            ]],
            'Red Sea' => ['name_ar' => 'البحر الأحمر', 'cities' => [
                'Hurghada' => 'الغردقة', 'Safaga' => 'سفاجا', 'Marsa Alam' => 'مرسى علم', 'El Gouna' => 'الجونة'
            ]],
            'South Sinai' => ['name_ar' => 'جنوب سيناء', 'cities' => [
                'Sharm El-Sheikh' => 'شرم الشيخ', 'Dahab' => 'دهب', 'Nuweiba' => 'نويبع', 'Taba' => 'طابا'
            ]],
            'North Sinai' => ['name_ar' => 'شمال سيناء', 'cities' => [
                'Arish' => 'العريش', 'Sheikh Zuweid' => 'الشيخ زويد'
            ]],
            'Matrouh' => ['name_ar' => 'مطروح', 'cities' => [
                'Marsa Matrouh' => 'مرسى مطروح', 'Siwa' => 'سيوة', 'El Alamein' => 'العلمين'
            ]],
            'New Valley' => ['name_ar' => 'الوادي الجديد', 'cities' => [
                'Kharga' => 'الخارجة', 'Dakhla' => 'الداخلة'
            ]],
        ];

        foreach ($locations as $govNameEn => $govData) {
            $governorate = Governorate::create([
                'name' => $govNameEn,
                'name_ar' => $govData['name_ar'],
                'is_active' => true,
            ]);

            foreach ($govData['cities'] as $cityNameEn => $cityNameAr) {
                $city = City::create([
                    'governorate_id' => $governorate->id,
                    'name' => $cityNameEn,
                    'name_ar' => $cityNameAr,
                    'is_active' => true,
                ]);

                // Create a default "Center" or "General" area for each city
                Area::create([
                    'city_id' => $city->id,
                    'name' => $cityNameEn . ' Center',
                    'name_ar' => $cityNameAr,
                    'is_active' => true,
                ]);
            }
        }
    }
}
