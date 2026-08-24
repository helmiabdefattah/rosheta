<?php

namespace App\Services;

use App\Models\Area;
use App\Models\City;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Governorate;
use App\Models\Laboratory;
use App\Models\Pharmacy;
use App\Models\Specialization;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Talks to the external providers-network API (GlobeMed provider directory) and
 * imports the returned doctors/clinics into the system as bookable records.
 *
 * The API filters by Arabic strings and must receive proper UTF-8 (Laravel's
 * JSON HTTP client does this); it returns the full matching set in one response
 * (`count` == number of results). We still page defensively in case that ever
 * changes, so "all pages" are always fetched.
 */
class ProvidersNetworkService
{
    public const ENDPOINT = 'https://test-abdelfattah74.pythonanywhere.com/portal/api/providers-network/search';

    /** Provider types the directory exposes (Arabic), most doctor-like first. */
    public const PROVIDER_TYPES = [
        'عيادات', 'اسنان', 'مراكز متخصصة', 'مستشفي', 'علاج طبيعي',
        'مراكز اشعة', 'معامل تحاليل', 'بصريات', 'صيدليات',
    ];

    /** Governorates (Arabic) as named by the API. */
    public const GOVERNORATES = [
        'القاهرة', 'الجيزة', 'القليوبية', 'الاسكندرية', 'الشرقية', 'المنوفية',
        'البحيرة', 'الدقهلية', 'السويس', 'الغربية', 'المنيا', 'الاسماعيلية',
        'بني سويف', 'اسيوط', 'بورسعيد', 'سوهاج', 'دمياط', 'قنا', 'الفيوم',
        'البحر الاحمر', 'كفر الشيخ', 'مطروح', 'اسوان', 'الاقصر', 'جنوب سيناء',
        'الوادي الجديد', 'شمال سيناء',
    ];

    /**
     * Search the provider directory. Accepts any of: governorate, city,
     * provider_type, network_tier, risk_carrier, search_query, quick_filters.
     * Returns ['count' => int, 'results' => array] with every page merged.
     */
    public function search(array $filters): array
    {
        $payload = ['lang' => 'ar', 'quick_filters' => $filters['quick_filters'] ?? []];
        foreach (['governorate', 'city', 'provider_type', 'network_tier', 'risk_carrier', 'search_query'] as $key) {
            $value = trim((string) ($filters[$key] ?? ''));
            if ($value !== '') {
                $payload[$key] = $value;
            }
        }

        // The API needs at least one filter (besides lang).
        if (count($payload) <= 2 && empty($payload['quick_filters'])) {
            return ['count' => 0, 'results' => [], 'error' => 'no_filter'];
        }

        $byId = [];
        $count = 0;
        $page = 1;
        $guard = 0;

        do {
            $body = $page === 1 ? $payload : $payload + ['page' => $page];
            $response = Http::timeout(120)->asJson()->acceptJson()->post(self::ENDPOINT, $body);
            if (! $response->successful()) {
                break;
            }

            $data = $response->json();
            if (isset($data['error'])) {
                return ['count' => 0, 'results' => [], 'error' => $data['error']];
            }

            $count = (int) ($data['count'] ?? 0);
            $results = $data['results'] ?? [];
            if (empty($results)) {
                break;
            }

            foreach ($results as $row) {
                $id = $row['provider_id'] ?? $row['Provider_ID'] ?? Str::uuid()->toString();
                $byId[$id] = $row; // de-dupes across pages
            }

            $page++;
            $guard++;
        } while (count($byId) < $count && $guard < 200);

        return ['count' => $count ?: count($byId), 'results' => array_values($byId)];
    }

    /**
     * Import provider rows as Doctor + Clinic records (directory listings — no
     * staff login). Idempotent by Provider_ID. Returns a summary.
     *
     * @return array{total:int, created:int, updated:int, skipped:int}
     */
    public function import(array $rows): array
    {
        $created = $updated = $skipped = 0;

        foreach ($rows as $row) {
            $result = $this->importOne($row);
            match ($result) {
                'created' => $created++,
                'updated' => $updated++,
                default => $skipped++,
            };
        }

        return [
            'total' => count($rows),
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /** Provider types that are lab facilities → Laboratory (with its `type`). */
    private const LAB_TYPES = [
        'معامل تحاليل' => 'test',
        'مراكز اشعة' => 'radiology',
    ];

    /**
     * Route a provider to the right entity by its provider_type: lab facilities
     * become Laboratory records, pharmacies become Pharmacy records, everything
     * else (عيادات / اسنان / مراكز متخصصة / …) becomes a Doctor + Clinic.
     *
     * Returns 'created' | 'updated' | 'skipped'.
     */
    private function importOne(array $r): string
    {
        $providerId = trim((string) ($r['provider_id'] ?? $r['Provider_ID'] ?? ''));
        $name = trim((string) ($r['provider_name_ar'] ?? $r['provider_name'] ?? ''));
        if ($providerId === '' || $name === '') {
            return 'skipped';
        }

        $type = trim((string) ($r['provider_type'] ?? ''));

        if (isset(self::LAB_TYPES[$type])) {
            return $this->importLab($r, $providerId, $name, self::LAB_TYPES[$type]);
        }
        if ($type === 'صيدليات') {
            return $this->importPharmacy($r, $providerId, $name);
        }

        return $this->importClinic($r, $providerId, $name);
    }

    /** A doctor practice → Doctor + Clinic. */
    private function importClinic(array $r, string $providerId, string $name): string
    {
        $governorate = $this->governorate((string) ($r['governorate'] ?? ''));
        $city = $governorate ? $this->city($governorate, (string) ($r['city'] ?? '')) : null;
        $specialization = $this->specialization((string) ($r['provider_specialty'] ?? ''));

        $doctor = Doctor::updateOrCreate(
            ['slug' => 'net-'.Str::lower($providerId)],
            [
                'name' => $name,
                'brief' => trim((string) ($r['provider_specialty'] ?? '')) ?: null,
                'specialization_id' => $specialization->id,
                'user_id' => null, // directory listing, no login
            ]
        );
        $wasNew = $doctor->wasRecentlyCreated;

        $clinic = Clinic::updateOrCreate(
            ['doctor_id' => $doctor->id, 'name' => $name],
            [
                'address' => trim((string) ($r['address'] ?? '')) ?: null,
                'phone_number' => $this->firstPhone((string) ($r['phone'] ?? '')) ?: null,
                'governorate_id' => $governorate?->id,
                'city_id' => $city?->id,
                'latitude' => $this->coord($r['coord_x'] ?? null),
                'longitude' => $this->coord($r['coord_y'] ?? null),
                'appointment_duration' => 30,
                'display_show_next_button' => true,
                'printer_language' => 'ar',
            ]
        );

        foreach (Clinic::DAYS as $day) {
            $closed = $day === 'friday';
            $clinic->workingHours()->updateOrCreate(
                ['day' => $day],
                ['from' => $closed ? null : '09:00', 'to' => $closed ? null : '17:00', 'is_closed' => $closed]
            );
        }
        $clinic->syncOpeningHoursFromWorkingHours();

        return $wasNew ? 'created' : 'updated';
    }

    /** A lab facility → Laboratory ($type = 'test' | 'radiology'). */
    private function importLab(array $r, string $providerId, string $name, string $type): string
    {
        $area = $this->areaFor($r);

        // Match by our own import marker (net:<provider_id>) so re-imports update
        // the same row and never collide with real, pre-existing labs.
        $lab = Laboratory::updateOrCreate(
            ['notes' => 'net:'.$providerId],
            [
                'name' => $name,
                'type' => $type,
                'area_id' => $area?->id,
                'phone' => $this->firstPhone((string) ($r['phone'] ?? '')) ?: null,
                'address' => trim((string) ($r['address'] ?? '')) ?: null,
                'lat' => $this->coord($r['coord_x'] ?? null),
                'lng' => $this->coord($r['coord_y'] ?? null),
                'is_active' => true,
                'user_id' => null,
            ]
        );

        return $lab->wasRecentlyCreated ? 'created' : 'updated';
    }

    /** A pharmacy → Pharmacy. */
    private function importPharmacy(array $r, string $providerId, string $name): string
    {
        $area = $this->areaFor($r);

        $pharmacy = Pharmacy::updateOrCreate(
            ['notes' => 'net:'.$providerId],
            [
                'name' => $name,
                'area_id' => $area?->id,
                'phone' => $this->firstPhone((string) ($r['phone'] ?? '')) ?: null,
                'address' => trim((string) ($r['address'] ?? '')) ?: null,
                'lat' => $this->coord($r['coord_x'] ?? null),
                'lng' => $this->coord($r['coord_y'] ?? null),
                'is_active' => true,
                'user_id' => null,
            ]
        );

        return $pharmacy->wasRecentlyCreated ? 'created' : 'updated';
    }

    /**
     * Resolve an Area for a lab/pharmacy row so it's searchable by governorate
     * (area → city → governorate). The API has no area, so we use one named
     * after the city under the resolved city.
     */
    private function areaFor(array $r): ?Area
    {
        $governorate = $this->governorate((string) ($r['governorate'] ?? ''));
        if (! $governorate) {
            return null;
        }
        $city = $this->city($governorate, (string) ($r['city'] ?? ''));
        if (! $city) {
            return null;
        }

        return Area::firstOrCreate(
            ['city_id' => $city->id, 'name_ar' => $city->name_ar],
            ['name' => $city->name],
        );
    }

    private function firstPhone(string $raw): string
    {
        $parts = preg_split('/[-،,\/]+/u', $raw) ?: [];

        return trim($parts[0] ?? '');
    }

    private function coord($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $f = (float) $value;

        return abs($f) > 0.0001 ? round($f, 8) : null;
    }

    private function specialization(string $specialty): Specialization
    {
        $specialty = trim($specialty) ?: 'عام';

        return Specialization::firstOrCreate(
            ['slug' => 'spec-'.md5($specialty)],
            ['name' => $specialty, 'brief' => 'تخصص '.$specialty],
        );
    }

    private function governorate(string $govAr): ?Governorate
    {
        $govAr = trim($govAr);
        if ($govAr === '') {
            return null;
        }

        return Governorate::where('name_ar', $govAr)->orWhere('name', $govAr)->first()
            ?? Governorate::create(['name' => $govAr, 'name_ar' => $govAr]);
    }

    private function city(Governorate $gov, string $cityAr): ?City
    {
        $cityAr = trim($cityAr);
        if ($cityAr === '') {
            return null;
        }

        return City::where('governorate_id', $gov->id)
            ->where(fn ($q) => $q->where('name_ar', $cityAr)->orWhere('name', $cityAr))
            ->first()
            ?? City::create(['governorate_id' => $gov->id, 'name' => $cityAr, 'name_ar' => $cityAr]);
    }
}
