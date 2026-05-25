<?php

namespace App\Http\Controllers;

use App\Models\CharitableOrganization;
use App\Models\Clinic;
use App\Models\ClientRequest;
use App\Models\Laboratory;
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

        // Process search if filters are provided
        // if (request()->has('governorate_id') && request()->has('provider_type')) {
        if (request()->has('provider_type')) {

            $governorateId = request('governorate_id');
            $providerType = request('provider_type');
            $cityId = request('city_id');
            $areaId = request('area_id');

            if ($providerType === 'charity') {
                $query = CharitableOrganization::with(['governorate', 'city', 'area'])
                    ->where('is_active', true);

                // Filter by governorate (required)
                $query->where('governorate_id', $governorateId);

                // Filter by city (optional)
                if ($cityId) {
                    $query->where('city_id', $cityId);
                }

                // Filter by area (optional)
                if ($areaId) {
                    $query->where('area_id', $areaId);
                }

                $results = $query->get();

                // Note: Charity organizations don't have lat/lng, so they won't appear on map
            } elseif ($providerType === 'doctor') {
                $query = Clinic::with(['doctor.specialization', 'governorate', 'city', 'area'])
                    ->whereHas('doctor')
                    ->where('governorate_id', $governorateId);

                if ($cityId) {
                    $query->where('city_id', $cityId);
                }
                if ($areaId) {
                    $query->where('area_id', $areaId);
                }

                $results = $query->orderBy('name')->get();

                foreach ($results as $clinic) {
                    if ($clinic->latitude && $clinic->longitude) {
                        $markers[] = [
                            'id' => $clinic->id,
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
            } elseif (in_array($providerType, ['radiology_lab', 'test_lab', 'laboratory'], true)) {
                $labTypes = $providerType === 'radiology_lab'
                    ? ['radiology', 'both']
                    : ($providerType === 'test_lab' ? ['test', 'both'] : ['radiology', 'test', 'both']);

                $query = Laboratory::with(['area.city.governorate'])
                    ->where('is_active', true)
                    ->whereIn('type', $labTypes)
                    ->whereNotNull('lat')
                    ->whereNotNull('lng');

                if ($governorateId) {
                    $query->whereHas('area.city', function ($q) use ($governorateId) {
                        $q->where('governorate_id', $governorateId);
                    });
                }

                if ($cityId) {
                    $query->whereHas('area', function ($q) use ($cityId) {
                        $q->where('city_id', $cityId);
                    });
                }

                if ($areaId) {
                    $query->where('area_id', $areaId);
                }

                $results = $query->get();

                foreach ($results as $lab) {
                    if ($lab->lat && $lab->lng) {
                        $markers[] = [
                            'id' => $lab->id,
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
            } elseif ($providerType === 'nursing') {
                $areaIdsQuery = Area::where('is_active', true);

                if ($areaId) {
                    $areaIdsQuery->where('id', $areaId);
                } elseif ($cityId) {
                    $areaIdsQuery->where('city_id', $cityId);
                } elseif ($governorateId) {
                    $areaIdsQuery->whereHas('city', function ($q) use ($governorateId) {
                        $q->where('governorate_id', $governorateId);
                    });
                }

                $matchingAreaIds = $areaIdsQuery->pluck('id')->all();

                $results = Nurse::with('user')
                    ->where('status', 'active')
                    ->get()
                    ->filter(function (Nurse $nurse) use ($matchingAreaIds) {
                        $nurseAreaIds = is_array($nurse->area_ids) ? $nurse->area_ids : [];

                        return count(array_intersect($nurseAreaIds, $matchingAreaIds)) > 0;
                    })
                    ->values();
            } else {
                $query = \App\Models\Pharmacy::with(['area.city.governorate'])
                    ->where('is_active', true)
                    ->whereNotNull('lat')
                    ->whereNotNull('lng');

                $query->whereHas('area.city', function ($q) use ($governorateId) {
                    $q->where('governorate_id', $governorateId);
                });

                if ($cityId) {
                    $query->whereHas('area', function ($q) use ($cityId) {
                        $q->where('city_id', $cityId);
                    });
                }

                if ($areaId) {
                    $query->where('area_id', $areaId);
                }

                $results = $query->get();

                foreach ($results as $pharmacy) {
                    if ($pharmacy->lat && $pharmacy->lng) {
                        $markers[] = [
                            'id' => $pharmacy->id,
                            'name' => $pharmacy->name,
                            'lat' => (float)$pharmacy->lat,
                            'lng' => (float)$pharmacy->lng,
                            'type' => 'pharmacy',
                            'phone' => $pharmacy->phone,
                            'address' => $pharmacy->address,
                            'logo' => null,
                        ];
                    }
                }
            }

            $cities = City::where('is_active', true)
                ->where('governorate_id', $governorateId)
                ->orderBy('name')
                ->get();

            $areas = Area::where('is_active', true)
                ->when($cityId, function ($q) use ($cityId) {
                    $q->where('city_id', $cityId);
                })
                ->with('city')
                ->orderBy('name')
                ->get();

            if (count($markers) > 0) {
                $avgLat = collect($markers)->avg('lat');
                $avgLng = collect($markers)->avg('lng');
                $mapCenter = ['lat' => $avgLat, 'lng' => $avgLng];
            }
        } else {
            if ($governorates->count() > 0) {
                $cities = City::where('is_active', true)
                    ->where('governorate_id', $governorates->first()->id)
                    ->orderBy('name')
                    ->get();
            }
        }

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
            'providerType'
        ));
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

