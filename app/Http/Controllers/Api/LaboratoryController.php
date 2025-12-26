<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LaboratoryController extends Controller
{
    /**
     * Search laboratories with filters
     * 
     * Query parameters:
     * - search: Search by laboratory name
     * - area_id: Filter by area
     * - city_id: Filter by city (through area relationship)
     * - governorate_id: Filter by governorate (through area->city relationship)
     * - lat: Latitude for nearby search
     * - lng: Longitude for nearby search
     * - radius: Radius in kilometers for nearby search (default: 10)
     * - is_active: Filter by active status (default: true)
     * - per_page: Number of results per page (default: 15)
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = Laboratory::with(['area.city.governorate', 'user'])
                ->where('is_active', $request->get('is_active', true));

            // Search by name
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Filter by area_id
            if ($request->has('area_id') && $request->area_id) {
                $query->where('area_id', $request->area_id);
            }

            // Filter by city_id (through area relationship)
            if ($request->has('city_id') && $request->city_id) {
                $query->whereHas('area', function ($q) use ($request) {
                    $q->where('city_id', $request->city_id);
                });
            }

            // Filter by governorate_id (through area->city relationship)
            if ($request->has('governorate_id') && $request->governorate_id) {
                $query->whereHas('area.city', function ($q) use ($request) {
                    $q->where('governorate_id', $request->governorate_id);
                });
            }

            // Nearby search using lat/lng
            if ($request->has('lat') && $request->has('lng')) {
                $lat = (float) $request->lat;
                $lng = (float) $request->lng;
                $radius = (float) ($request->get('radius', 10)); // Default 10km

                // Haversine formula for distance calculation
                $query->selectRaw(
                    "laboratories.*,
                    (6371 * acos(
                        cos(radians(?))
                        * cos(radians(laboratories.lat))
                        * cos(radians(laboratories.lng) - radians(?))
                        + sin(radians(?))
                        * sin(radians(laboratories.lat))
                    )) AS distance",
                    [$lat, $lng, $lat]
                )
                ->having('distance', '<=', $radius)
                ->whereNotNull('laboratories.lat')
                ->whereNotNull('laboratories.lng')
                ->orderBy('distance');
            } else {
                // Default ordering when no location provided
                $query->orderBy('laboratories.name');
            }

            $perPage = (int) $request->get('per_page', 15);
            $laboratories = $query->paginate($perPage);

            // Transform the data for API response
            $data = $laboratories->map(function ($laboratory) {
                return $this->transformLaboratory($laboratory);
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $laboratories->currentPage(),
                    'last_page' => $laboratories->lastPage(),
                    'per_page' => $laboratories->perPage(),
                    'total' => $laboratories->total(),
                    'from' => $laboratories->firstItem(),
                    'to' => $laboratories->lastItem(),
                ],
                'message' => 'Laboratories retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve laboratories.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific laboratory by ID
     */
    public function show($id): JsonResponse
    {
        try {
            $laboratory = Laboratory::with(['area.city.governorate', 'user', 'testPrices.medicalTest'])
                ->where('is_active', true)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $this->transformLaboratory($laboratory, true),
                'message' => 'Laboratory retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Laboratory not found.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Transform laboratory data for API response
     */
    private function transformLaboratory($laboratory, $includeDetails = false)
    {
        $data = [
            'id' => $laboratory->id,
            'name' => $laboratory->name,
            'phone' => $laboratory->phone,
            'email' => $laboratory->email,
            'address' => $laboratory->address,
            'latitude' => $laboratory->lat ? (float) $laboratory->lat : null,
            'longitude' => $laboratory->lng ? (float) $laboratory->lng : null,
            'license_number' => $laboratory->license_number,
            'manager_name' => $laboratory->manager_name,
            'manager_license' => $laboratory->manager_license,
            'opening_time' => $laboratory->opening_time,
            'closing_time' => $laboratory->closing_time,
            'is_active' => $laboratory->is_active,
            'logo_url' => $laboratory->getFirstMediaUrl('logo') ?: null,
            'area' => null,
            'city' => null,
            'governorate' => null,
        ];

        // Include distance if calculated
        if (isset($laboratory->distance)) {
            $data['distance'] = round((float) $laboratory->distance, 2);
        }

        // Include area information
        if ($laboratory->area) {
            $data['area'] = [
                'id' => $laboratory->area->id,
                'name' => $laboratory->area->name,
                'name_ar' => $laboratory->area->name_ar ?? null,
            ];

            // Include city information
            if ($laboratory->area->city) {
                $data['city'] = [
                    'id' => $laboratory->area->city->id,
                    'name' => $laboratory->area->city->name,
                    'name_ar' => $laboratory->area->city->name_ar ?? null,
                ];

                // Include governorate information
                if ($laboratory->area->city->governorate) {
                    $data['governorate'] = [
                        'id' => $laboratory->area->city->governorate->id,
                        'name' => $laboratory->area->city->governorate->name,
                        'name_ar' => $laboratory->area->city->governorate->name_ar ?? null,
                    ];
                }
            }
        }

        // Include owner information if available
        if ($laboratory->user) {
            $data['owner'] = [
                'id' => $laboratory->user->id,
                'name' => $laboratory->user->name,
                'email' => $laboratory->user->email,
            ];
        }

        // Include test prices if detailed view
        if ($includeDetails && $laboratory->testPrices) {
            $data['test_prices'] = $laboratory->testPrices->map(function ($testPrice) {
                return [
                    'id' => $testPrice->id,
                    'test_id' => $testPrice->medical_test_id,
                    'test_name_en' => $testPrice->medicalTest->test_name_en ?? null,
                    'test_name_ar' => $testPrice->medicalTest->test_name_ar ?? null,
                    'price' => (float) $testPrice->price,
                ];
            });
        }

        return $data;
    }
}

