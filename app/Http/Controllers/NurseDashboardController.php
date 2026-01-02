<?php

// app/Http/Controllers/Nurse/NurseDashboardController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Client;
use App\Models\Nurse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NurseDashboardController extends Controller
{
    public function index()
    {
        // 1. Find the client
        $client = Auth::guard('client')->user();

        // 2. Check if client has a nurse_id
        if (!$client->nurse_id) {
            abort(404, 'No nurse assigned to this client');
        }

        // 3. Find the nurse using client's nurse_id
        $nurse = Nurse::with('client')->findOrFail($client->nurse_id);

        // 4. Get nurse's offers and visits
        $offers = $nurse->offers()->with('request')->latest()->paginate(10);
        $visits = $nurse->visits()->with('request')->latest()->paginate(10);

        // 5. Build areaMap for this single nurse (CORRECTED)
        $allAreaIds = is_array($nurse->area_ids) ? $nurse->area_ids : [];
        $areaMap = collect();

        if (!empty($allAreaIds)) {
            $areaMap = Area::with('city.governorate')
                ->whereIn('id', $allAreaIds)
                ->get()
                ->keyBy('id');
        }

        // 6. Pass both client and nurse to view
        return view('nurse.dashboard', compact('client', 'areaMap', 'nurse', 'offers', 'visits'));
    }//    public function index()
//    {
//
//        $nurses = Nurse::with('client')->orderByDesc('id')->paginate(15);
//
//        // Build a map of area_id => Area (with city/governorate) for display
//        $allAreaIds = collect($nurses->items())
//            ->flatMap(function ($n) {
//                return is_array($n->area_ids) ? $n->area_ids : [];
//            })
//            ->filter()
//            ->unique()
//            ->values();
//
//        $areaMap = $allAreaIds->isNotEmpty()
//            ? Area::with('city.governorate')->whereIn('id', $allAreaIds)->get()->keyBy('id')
//            : collect();
//
//        return view('nurse.dashboard', compact('nurses', 'areaMap'));
//    }
}
