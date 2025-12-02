<?php

namespace App\Http\Controllers\Api;

use App\Models\Area;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AreaController extends Controller
{
    /**
     * Display a listing of areas.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Area::with(['city'])
                ->where('is_active', true);

            // Filter by city_id if provided
            if ($request->has('city_id')) {
                $query->where('city_id', $request->city_id);
            }

            // Search by name or name_ar
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%");
                });
            }

            $areas = $query->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $areas,
                'message' => 'Areas retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve areas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
