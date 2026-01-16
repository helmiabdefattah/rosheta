<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Pharmacy;
use App\Models\Area;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;

class ClientServiceProviderController extends Controller
{
    public function index(Request $request)
    {
        // Get filter options (always needed)
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $cities = collect();
        $areas = collect();
        $results = collect();
        $markers = [];
        $mapCenter = ['lat' => 30.0444, 'lng' => 31.2357];
        $governorateId = $request->governorate_id;
        $cityId = $request->city_id;
        $areaId = $request->area_id;
        $providerType = $request->provider_type;

        // Only process search if required fields are provided
        if ($request->filled('governorate_id') && $request->filled('provider_type')) {
            // Validate required fields
            $request->validate([
                'governorate_id' => 'required|exists:governorates,id',
                'provider_type' => 'required|in:laboratory,pharmacy',
            ]);

            $governorateId = $request->governorate_id;
            $providerType = $request->provider_type;
            $cityId = $request->city_id;
            $areaId = $request->area_id;

        if ($providerType === 'laboratory') {
            $query = Laboratory::with(['area.city.governorate'])
                ->where('is_active', true)
                ->whereNotNull('lat')
                ->whereNotNull('lng');

            // Filter by governorate (required)
            $query->whereHas('area.city', function ($q) use ($governorateId) {
                $q->where('governorate_id', $governorateId);
            });

            // Filter by city (optional)
            if ($cityId) {
                $query->whereHas('area', function ($q) use ($cityId) {
                    $q->where('city_id', $cityId);
                });
            }

            // Filter by area (optional)
            if ($areaId) {
                $query->where('area_id', $areaId);
            }

            $results = $query->get();

            // Prepare markers for map
            foreach ($results as $lab) {
                if ($lab->lat && $lab->lng) {
                    $markers[] = [
                        'id' => $lab->id,
                        'name' => $lab->name,
                        'lat' => (float)$lab->lat,
                        'lng' => (float)$lab->lng,
                        'type' => 'laboratory',
                        'phone' => $lab->phone,
                        'address' => $lab->address,
                        'logo' => $lab->logo ? asset('storage/' . $lab->logo) : null,
                    ];
                }
            }
        } else {
            $query = Pharmacy::with(['area.city.governorate'])
                ->where('is_active', true)
                ->whereNotNull('lat')
                ->whereNotNull('lng');

            // Filter by governorate (required)
            $query->whereHas('area.city', function ($q) use ($governorateId) {
                $q->where('governorate_id', $governorateId);
            });

            // Filter by city (optional)
            if ($cityId) {
                $query->whereHas('area', function ($q) use ($cityId) {
                    $q->where('city_id', $cityId);
                });
            }

            // Filter by area (optional)
            if ($areaId) {
                $query->where('area_id', $areaId);
            }

            $results = $query->get();

            // Prepare markers for map
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
                        'logo' => null, // Pharmacy doesn't have logo in the model
                    ];
                }
            }
        }

            // Get cities for selected governorate
            $cities = City::where('is_active', true)
                ->where('governorate_id', $governorateId)
                ->orderBy('name')
                ->get();

            // Get areas (filtered by city if provided)
            $areas = Area::where('is_active', true)
                ->when($cityId, function ($q) use ($cityId) {
                    $q->where('city_id', $cityId);
                })
                ->with('city')
                ->orderBy('name')
                ->get();

            // Calculate map center (average of all markers or default to Cairo)
            if (count($markers) > 0) {
                $avgLat = collect($markers)->avg('lat');
                $avgLng = collect($markers)->avg('lng');
                $mapCenter = ['lat' => $avgLat, 'lng' => $avgLng];
            }
        } else {
            // If no search performed, get cities for first governorate (if any)
            if ($governorates->count() > 0) {
                $cities = City::where('is_active', true)
                    ->where('governorate_id', $governorates->first()->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('client.service-providers.index', compact(
            'results',
            'markers',
            'mapCenter',
            'governorates',
            'cities',
            'areas',
            'governorateId',
            'cityId',
            'areaId',
            'providerType'
        ));
    }
}
