<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Area;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;

class ClientLaboratoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Laboratory::with(['area.city.governorate'])
            ->where('is_active', true);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by area
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        // Filter by city (through area relationship)
        if ($request->filled('city_id')) {
            $query->whereHas('area', function ($q) use ($request) {
                $q->where('city_id', $request->city_id);
            });
        }

        // Filter by governorate (through area.city relationship)
        if ($request->filled('governorate_id')) {
            $query->whereHas('area.city', function ($q) use ($request) {
                $q->where('governorate_id', $request->governorate_id);
            });
        }

        $laboratories = $query->paginate(12)->withQueryString();

        // Get filter options
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $cities = City::where('is_active', true)
            ->when($request->filled('governorate_id'), function ($q) use ($request) {
                $q->where('governorate_id', $request->governorate_id);
            })
            ->orderBy('name')
            ->get();
        $areas = Area::where('is_active', true)
            ->when($request->filled('city_id'), function ($q) use ($request) {
                $q->where('city_id', $request->city_id);
            })
            ->with('city')
            ->orderBy('name')
            ->get();

        return view('client.laboratories.index', compact(
            'laboratories',
            'governorates',
            'cities',
            'areas'
        ));
    }
}
