<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Governorate;
use App\Models\MedicalCenter;
use Illuminate\Database\Seeder;

/**
 * Seeds a set of real medical centers (idempotent — safe to re-run).
 *
 * La Rose Wellness Hub is seeded as two branches (Maadi + Fifth Settlement),
 * each with its clinics; the weekly session schedule the client provided is
 * kept inside each clinic's name (the schema has no per-day schedule column).
 * A handful of other real Cairo centers are added too. No doctors are attached
 * (doctor_id stays null) — an admin can assign doctors later.
 *
 * Run with:  php artisan db:seed --class=MedicalCentersSeeder
 */
class MedicalCentersSeeder extends Seeder
{
    public function run(): void
    {
        $centers = [
            [
                'name' => 'La Rose Wellness Hub – Maadi',
                'phone_number' => '+201040661893',
                'address' => '14 El-Mokhtar St, off El-Nasr St, behind QNB Bank, New Maadi, Cairo',
                'latitude' => 29.9560, 'longitude' => 31.2700,
                'open_time' => '14:00:00', 'close_time' => '20:00:00',
                'area' => 'Maadi', 'city' => 'Cairo',
                'clinics' => [
                    'الباطنة والسونار — السبت من ٤ إلى ٧',
                    'التغذية العلاجية — السبت والاثنين من ٢ إلى ٨، والثلاثاء من ٣ إلى ٨',
                ],
            ],
            [
                'name' => 'La Rose Wellness Hub – Fifth Settlement',
                'phone_number' => '+201040661893',
                'address' => 'CMC Medical Mall, 4th floor, North 90th St, behind Air Force Specialized Hospital, Fifth Settlement, New Cairo',
                'latitude' => 30.0290, 'longitude' => 31.4700,
                'open_time' => '15:30:00', 'close_time' => '20:00:00',
                'area' => 'Fifth Settlement', 'city' => 'New Cairo',
                'clinics' => [
                    'الباطنة والتغذية العلاجية — الأربعاء من ٣:٣٠ إلى ٨',
                ],
            ],
            [
                'name' => 'Town Hospital – New Cairo',
                'phone_number' => '15276',
                'address' => 'North Teseen (90th) St, Fifth Settlement, New Cairo, Cairo',
                'latitude' => 30.0180, 'longitude' => 31.4360,
                'open_time' => null, 'close_time' => null,
                'area' => 'Fifth Settlement', 'city' => 'New Cairo',
                'clinics' => [
                    'الباطنة العامة',
                    'الأطفال وحديثي الولادة',
                    'العظام',
                    'معمل تحاليل وأشعة',
                    'طوارئ ٢٤ ساعة',
                ],
            ],
            [
                'name' => 'Oasis Clinics',
                'phone_number' => null,
                'address' => 'New Cairo, Cairo',
                'latitude' => 30.0300, 'longitude' => 31.4720,
                'open_time' => '10:00:00', 'close_time' => '22:00:00',
                'area' => 'New Cairo', 'city' => 'New Cairo',
                'clinics' => [
                    'الجلدية والتجميل',
                    'الأسنان',
                    'النساء والتوليد',
                    'الباطنة',
                    'الأطفال',
                ],
            ],
            [
                'name' => 'Cairo Care Specialist Clinics',
                'phone_number' => null,
                'address' => 'Nasr City, Cairo',
                'latitude' => 30.0566, 'longitude' => 31.3480,
                'open_time' => '12:00:00', 'close_time' => '22:00:00',
                'area' => 'Nasr City', 'city' => 'Cairo',
                'clinics' => [
                    'المخ والأعصاب',
                    'القلب والأوعية الدموية',
                    'الجلدية',
                    'الباطنة',
                ],
            ],
            [
                'name' => 'International Medical Center (IMC)',
                'phone_number' => null,
                'address' => 'Km 42 Cairo-Ismailia Desert Road, El Shorouk, Cairo',
                'latitude' => 30.1300, 'longitude' => 31.6100,
                'open_time' => null, 'close_time' => null,
                'area' => 'El Shorouk', 'city' => 'El Shorouk',
                'clinics' => [
                    'الجراحة العامة',
                    'القلب',
                    'الأشعة التشخيصية',
                    'طوارئ ٢٤ ساعة',
                ],
            ],
        ];

        foreach ($centers as $data) {
            [$govId, $cityId, $areaId] = $this->resolveLocation($data['area'], $data['city']);

            $center = MedicalCenter::updateOrCreate(
                ['name' => $data['name']],
                [
                    'phone_number' => $data['phone_number'],
                    'address' => $data['address'],
                    'governorate_id' => $govId,
                    'city_id' => $cityId,
                    'area_id' => $areaId,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'open_time' => $data['open_time'],
                    'close_time' => $data['close_time'],
                    'is_active' => true,
                ]
            );

            foreach ($data['clinics'] as $clinicName) {
                Clinic::updateOrCreate(
                    ['medical_center_id' => $center->id, 'name' => $clinicName],
                    [
                        'doctor_id' => null,
                        'governorate_id' => $govId,
                        'city_id' => $cityId,
                        'area_id' => $areaId,
                        'address' => $data['address'],
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                        'medical_examination_price' => 0,
                    ]
                );
            }

            $this->command?->info("Seeded center: {$center->name} ({$center->clinics()->count()} clinics)");
        }
    }

    /**
     * Best-effort resolution of governorate/city/area ids from names (matching
     * both English and Arabic name columns). Returns [gov, city, area], any of
     * which may be null when no match exists — the center is still created.
     */
    private function resolveLocation(?string $areaName, ?string $cityName): array
    {
        $govId = $cityId = $areaId = null;

        if ($areaName) {
            $area = Area::query()
                ->where('name', 'like', "%{$areaName}%")
                ->orWhere('name_ar', 'like', "%{$areaName}%")
                ->with('city')
                ->first();
            if ($area) {
                $areaId = $area->id;
                $cityId = $area->city_id;
                $govId = $area->city?->governorate_id;
            }
        }

        if (! $cityId && $cityName) {
            $city = City::query()
                ->where('name', 'like', "%{$cityName}%")
                ->orWhere('name_ar', 'like', "%{$cityName}%")
                ->first();
            if ($city) {
                $cityId = $city->id;
                $govId = $city->governorate_id;
            }
        }

        if (! $govId) {
            $gov = Governorate::query()
                ->where('name', 'like', '%Cairo%')
                ->orWhere('name_ar', 'like', '%القاهرة%')
                ->first();
            $govId = $gov?->id;
        }

        return [$govId, $cityId, $areaId];
    }
}
