<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\User;
use App\Models\Area;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryController extends Controller
{
    public function create()
    {
        $users = User::all();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();
        $governorates = Governorate::where('is_active', true)->get();
        $cities = City::where('is_active', true)->get();

        return view('laboratories.create', compact('users', 'areas', 'governorates', 'cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'area_id' => 'nullable|exists:areas,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'license_number' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_license' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Handle location array if provided
        if ($request->has('location') && is_array($request->location)) {
            $validated['location'] = $request->location;
        }

        Laboratory::create($validated);

        return redirect()->route('filament.admin.resources.laboratories.index')
            ->with('success', 'Laboratory created successfully.');
    }

    public function edit(Laboratory $laboratory)
    {
        $users = User::all();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();
        $governorates = Governorate::where('is_active', true)->get();
        $cities = City::where('is_active', true)->get();

        return view('laboratories.edit', compact('laboratory', 'users', 'areas', 'governorates', 'cities'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'area_id' => 'nullable|exists:areas,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'license_number' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_license' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Handle location array if provided
        if ($request->has('location') && is_array($request->location)) {
            $validated['location'] = $request->location;
        }

        $laboratory->update($validated);

        return redirect()->route('filament.admin.resources.laboratories.index')
            ->with('success', 'Laboratory updated successfully.');
    }
}
