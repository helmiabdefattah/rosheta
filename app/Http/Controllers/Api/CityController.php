<?php

namespace App\Http\Controllers\Api;

use App\Models\City;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * Display a listing of cities.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = City::with(['governorate'])
                ->where('is_active', true);

            // Filter by governorate_id if provided
            if ($request->has('governorate_id')) {
                $query->where('governorate_id', $request->governorate_id);
            }

            // Search by name or name_ar
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%");
                });
            }

            $cities = $query->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cities,
                'message' => 'Cities retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cities.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
