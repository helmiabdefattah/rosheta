<?php

namespace App\Http\Controllers;

use App\Models\CharitableOrganization;
use App\Models\Clinic;
use App\Models\ClientRequest;
use App\Models\Laboratory;
use App\Models\MedicalCenter;
use App\Models\MedicalTest;
use App\Models\NurseVisit;
use App\Models\Order;
use App\Models\Governorate;
use App\Models\City;
use App\Models\Area;
use App\Models\Nurse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();

        // Statistics
        $stats = [
            'total_requests' => ClientRequest::where('client_id', $client->id)->count(),
            'pending_requests' => ClientRequest::where('client_id', $client->id)
                ->where('status', 'pending')
                ->count(),
            'total_orders' => Order::whereHas('request', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->count(),
            'active_orders' => Order::whereHas('request', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->whereIn('status', ['pending', 'processing', 'shipped'])->count(),
            'scheduled_visits' => NurseVisit::whereHas('request', fn($q) => $q->where('client_id', $client->id))
                ->where('status', 'scheduled')
                ->count(),
            'test_results' => \App\Models\Offer::where('status', 'accepted')
                ->whereIn('request_type', ['test', 'radiology'])
                ->whereHas('request', function($q) use ($client) {
                    $q->where('client_id', $client->id);
                })
                ->whereHas('attachments')
                ->count(),
        ];

        // Recent requests (lines with item names, offers count, provider for dashboard cards)
        $recentRequests = ClientRequest::where('client_id', $client->id)
            ->with([
                'provider',
                'lines' => static function ($q) {
                    $q->with(['medicine', 'medicalTest'])->orderBy('id');
                },
            ])
            ->withCount('offers')
            ->latest()
            ->limit(5)
            ->get();

        // Client visits with reviews
        $visits = NurseVisit::with(['request.client', 'review', 'offer'])
            ->whereHas('request', fn($q) => $q->where('client_id', $client->id))
            ->orderByDesc('visit_datetime')
            ->paginate(10); // pagination optional

        // Available bonus points (not used and active)
        $availableBonusPoints = \App\Models\BonusPoint::where('client_id', $client->id)
            ->where('used', false)
            ->where('status', 'active')
            ->sum('points');

        // Service Provider Search Data
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $cities = collect();
        $areas = collect();
        $results = collect();
        $markers = [];
        $mapCenter = ['lat' => 30.0444, 'lng' => 31.2357];
        $governorateId = request('governorate_id');
        $cityId = request('city_id');
        $areaId = request('area_id');
        $providerType = request('provider_type');

        // Process search when the filter form is submitted. provider_type is no
        // longer mandatory: an empty value (or "all") searches every type at
        // once and merges the results, each item tagged with its own `_ptype`.
        if (request()->has('provider_type')) {

            $governorateId = request('governorate_id');
            $providerType = request('provider_type');
            $cityId = request('city_id');
            $areaId = request('area_id');

            $type = $providerType ?: 'all';

            $absorb = function (array $bundle) use (&$results, &$markers) {
                $results = $results->concat($bundle['results']);
                $markers = array_merge($markers, $bundle['markers']);
            };

            if ($type === 'all') {
                $absorb($this->queryDoctors($governorateId, $cityId, $areaId));
                $absorb($this->queryMedicalCenters($governorateId, $cityId, $areaId));
                $absorb($this->queryLaboratories('laboratory', $governorateId, $cityId, $areaId));
                $absorb($this->queryPharmacies($governorateId, $cityId, $areaId));
                $absorb($this->queryNursing($governorateId, $cityId, $areaId));
                $absorb($this->queryCharity($governorateId, $cityId, $areaId));
            } elseif ($type === 'doctor') {
                $absorb($this->queryDoctors($governorateId, $cityId, $areaId));
            } elseif ($type === 'medical_center') {
                $absorb($this->queryMedicalCenters($governorateId, $cityId, $areaId));
            } elseif (in_array($type, ['radiology_lab', 'test_lab', 'laboratory'], true)) {
                $absorb($this->queryLaboratories($type, $governorateId, $cityId, $areaId));
            } elseif ($type === 'nursing') {
                $absorb($this->queryNursing($governorateId, $cityId, $areaId));
            } elseif ($type === 'charity') {
                $absorb($this->queryCharity($governorateId, $cityId, $areaId));
            } else {
                $absorb($this->queryPharmacies($governorateId, $cityId, $areaId));
            }

            $cities = City::where('is_active', true)
                ->when($governorateId, fn ($q) => $q->where('governorate_id', $governorateId))
                ->orderBy('name')
                ->get();

            $areas = Area::where('is_active', true)
                ->when($cityId, fn ($q) => $q->where('city_id', $cityId))
                ->with('city')
                ->orderBy('name')
                ->get();

            if (count($markers) > 0) {
                $mapCenter = [
                    'lat' => collect($markers)->avg('lat'),
                    'lng' => collect($markers)->avg('lng'),
                ];
            }
        } else {
            if ($governorates->count() > 0) {
                $cities = City::where('is_active', true)
                    ->where('governorate_id', $governorates->first()->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        // The patient's upcoming clinic appointments — used by the quick-attach
        // modal to (optionally) tie an uploaded file to a specific visit.
        $clinicAppointments = \App\Models\Appointment::where('client_id', $client->id)
            ->whereDate('scheduled_at', '>=', today())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->with('clinic')
            ->orderBy('scheduled_at')
            ->get();

        return view('client.dashboard', compact(
            'stats',
            'recentRequests',
            'visits',
            'availableBonusPoints',
            'governorates',
            'cities',
            'areas',
            'results',
            'markers',
            'mapCenter',
            'governorateId',
            'cityId',
            'areaId',
            'providerType',
            'clinicAppointments'
        ));
    }

    // ── Per-type provider search helpers ────────────────────────────────
    // Each returns ['results' => Collection, 'markers' => array]; every result
    // model is tagged with a `_ptype` attribute so a merged ("all types")
    // result set still knows what each card is. Governorate/city/area filters
    // are all optional, which is what lets "search all types" work without a
    // governorate selected.

    /** Clinics that have a doctor assigned (a bookable doctor's clinic). */
    private function queryDoctors($gov, $city, $area): array
    {
        $clinics = Clinic::with(['doctor.specialization', 'doctor.user', 'governorate', 'city', 'area'])
            ->whereHas('doctor')
            ->when($gov, fn ($q) => $q->where('governorate_id', $gov))
            ->when($city, fn ($q) => $q->where('city_id', $city))
            ->when($area, fn ($q) => $q->where('area_id', $area))
            ->orderBy('name')
            ->get();

        $markers = [];
        foreach ($clinics as $clinic) {
            $clinic->_ptype = 'doctor';
            if ($clinic->latitude && $clinic->longitude) {
                $markers[] = [
                    'id' => 'doctor-' . $clinic->id,
                    'name' => $clinic->name,
                    'lat' => (float) $clinic->latitude,
                    'lng' => (float) $clinic->longitude,
                    'type' => 'clinic',
                    'address' => $clinic->address ?? null,
                    'doctor_name' => $clinic->doctor?->name,
                    'specialization' => $clinic->doctor?->specialization?->name,
                    'book_url' => route('client.doctor-reservation.book', $clinic),
                ];
            }
        }

        return ['results' => $clinics, 'markers' => $markers];
    }

    /** Medical centers (each holds several clinics, doctor optional). */
    private function queryMedicalCenters($gov, $city, $area): array
    {
        $centers = MedicalCenter::with([
                'clinics.doctor.specialization', 'clinics.doctor.user',
                'governorate', 'city', 'area',
            ])
            ->where('is_active', true)
            ->when($gov, fn ($q) => $q->where('governorate_id', $gov))
            ->when($city, fn ($q) => $q->where('city_id', $city))
            ->when($area, fn ($q) => $q->where('area_id', $area))
            ->orderBy('name')
            ->get();

        $markers = [];
        foreach ($centers as $center) {
            $center->_ptype = 'medical_center';
            if ($center->latitude && $center->longitude) {
                $markers[] = [
                    'id' => 'medical_center-' . $center->id,
                    'name' => $center->name,
                    'lat' => (float) $center->latitude,
                    'lng' => (float) $center->longitude,
                    'type' => 'medical_center',
                    'address' => $center->address,
                    'phone' => $center->phone_number,
                    'clinics_count' => $center->clinics->count(),
                ];
            }
        }

        return ['results' => $centers, 'markers' => $markers];
    }

    /** Laboratories. $type is radiology_lab | test_lab | laboratory (both). */
    private function queryLaboratories($type, $gov, $city, $area): array
    {
        $labTypes = $type === 'radiology_lab'
            ? ['radiology', 'both']
            : ($type === 'test_lab' ? ['test', 'both'] : ['radiology', 'test', 'both']);

        $labs = Laboratory::with(['area.city.governorate', 'user'])
            ->where('is_active', true)
            ->whereIn('type', $labTypes)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->when($gov, fn ($q) => $q->whereHas('area.city', fn ($c) => $c->where('governorate_id', $gov)))
            ->when($city, fn ($q) => $q->whereHas('area', fn ($a) => $a->where('city_id', $city)))
            ->when($area, fn ($q) => $q->where('area_id', $area))
            ->get();

        $markers = [];
        foreach ($labs as $lab) {
            $lab->_ptype = $type;
            if ($lab->lat && $lab->lng) {
                $markers[] = [
                    'id' => $type . '-' . $lab->id,
                    'name' => $lab->name,
                    'lat' => (float) $lab->lat,
                    'lng' => (float) $lab->lng,
                    'type' => 'laboratory',
                    'phone' => $lab->phone,
                    'address' => $lab->address,
                    'logo' => $lab->logo ? asset('storage/' . $lab->logo) : null,
                ];
            }
        }

        return ['results' => $labs, 'markers' => $markers];
    }

    /** Home-nursing providers (no map markers — they serve areas, not a point). */
    private function queryNursing($gov, $city, $area): array
    {
        $areaIdsQuery = Area::where('is_active', true);
        if ($area) {
            $areaIdsQuery->where('id', $area);
        } elseif ($city) {
            $areaIdsQuery->where('city_id', $city);
        } elseif ($gov) {
            $areaIdsQuery->whereHas('city', fn ($q) => $q->where('governorate_id', $gov));
        }
        $matchingAreaIds = $areaIdsQuery->pluck('id')->all();

        $nurses = Nurse::with('user')
            ->where('status', 'active')
            ->get()
            ->filter(function (Nurse $nurse) use ($matchingAreaIds) {
                $nurseAreaIds = is_array($nurse->area_ids) ? $nurse->area_ids : [];

                return count(array_intersect($nurseAreaIds, $matchingAreaIds)) > 0;
            })
            ->each(fn ($nurse) => $nurse->_ptype = 'nursing')
            ->values();

        return ['results' => $nurses, 'markers' => []];
    }

    /** Pharmacies. */
    private function queryPharmacies($gov, $city, $area): array
    {
        $pharmacies = \App\Models\Pharmacy::with(['area.city.governorate', 'user'])
            ->where('is_active', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->when($gov, fn ($q) => $q->whereHas('area.city', fn ($c) => $c->where('governorate_id', $gov)))
            ->when($city, fn ($q) => $q->whereHas('area', fn ($a) => $a->where('city_id', $city)))
            ->when($area, fn ($q) => $q->where('area_id', $area))
            ->get();

        $markers = [];
        foreach ($pharmacies as $pharmacy) {
            $pharmacy->_ptype = 'pharmacy';
            if ($pharmacy->lat && $pharmacy->lng) {
                $markers[] = [
                    'id' => 'pharmacy-' . $pharmacy->id,
                    'name' => $pharmacy->name,
                    'lat' => (float) $pharmacy->lat,
                    'lng' => (float) $pharmacy->lng,
                    'type' => 'pharmacy',
                    'phone' => $pharmacy->phone,
                    'address' => $pharmacy->address,
                    'logo' => null,
                ];
            }
        }

        return ['results' => $pharmacies, 'markers' => $markers];
    }

    /** Charitable organizations (no lat/lng, so no map markers). */
    private function queryCharity($gov, $city, $area): array
    {
        $orgs = CharitableOrganization::with(['governorate', 'city', 'area'])
            ->where('is_active', true)
            ->when($gov, fn ($q) => $q->where('governorate_id', $gov))
            ->when($city, fn ($q) => $q->where('city_id', $city))
            ->when($area, fn ($q) => $q->where('area_id', $area))
            ->get()
            ->each(fn ($org) => $org->_ptype = 'charity');

        return ['results' => $orgs, 'markers' => []];
    }

    /**
     * JSON autocomplete for medical tests (client dashboard search bar).
     */
    public function searchMedicalTests(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $locale = app()->getLocale();
        $data = MedicalTest::query()
            ->where(function ($qq) use ($q) {
                $qq->where('test_name_ar', 'like', '%'.$q.'%')
                    ->orWhere('test_name_en', 'like', '%'.$q.'%');
            })
            ->orderBy('test_name_en')
            ->limit(20)
            ->get(['id', 'test_name_ar', 'test_name_en', 'type'])
            ->map(function ($t) use ($locale) {
                $label = $locale === 'ar'
                    ? ($t->test_name_ar ?: $t->test_name_en)
                    : ($t->test_name_en ?: $t->test_name_ar);
                $type = $t->type === 'radiology' ? 'radiology' : 'test';

                return [
                    'id' => $t->id,
                    'label' => $label,
                    'type' => $type,
                    'url' => route('client.test-requests.create', ['type' => $type]),
                ];
            });

        return response()->json(['data' => $data]);
    }

    /**
     * JSON autocomplete for laboratories (link to lab offers page).
     */
    public function searchLaboratories(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $data = Laboratory::query()
            ->where('is_active', true)
            ->where('name', 'like', '%'.$q.'%')
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name'])
            ->map(fn ($lab) => [
                'id' => $lab->id,
                'label' => $lab->name,
                'url' => route('client.laboratories.offers', $lab),
            ]);

        return response()->json(['data' => $data]);
    }
}

