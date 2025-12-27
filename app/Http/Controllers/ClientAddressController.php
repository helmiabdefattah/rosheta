<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAddressController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();
        
        $addresses = ClientAddress::where('client_id', $client->id)
            ->with(['city', 'area'])
            ->latest()
            ->get();
        
        return view('client.addresses.index', compact('addresses'));
    }

    public function create()
    {
        $cities = City::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('client.addresses.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        // Build location JSON if lat/lng exist
        $location = null;
        if (isset($validated['lat'], $validated['lng'])) {
            $location = [
                'lat' => (float) $validated['lat'],
                'lng' => (float) $validated['lng'],
            ];
        }

        // Remove lat/lng from validated to avoid mass assignment issues
        unset($validated['lat'], $validated['lng']);

        // Create the address
        $address = ClientAddress::create([
            ...$validated,
            'client_id' => $client->id,
            'location' => $location,
        ]);

        return redirect()->route('client.addresses.index')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم إضافة العنوان بنجاح' 
                : 'Address added successfully');
    }

    public function edit(ClientAddress $address)
    {
        $client = Auth::guard('client')->user();
        
        // Ensure the address belongs to the authenticated client
        if ($address->client_id !== $client->id) {
            abort(403);
        }
        
        $cities = City::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $areas = Area::where('city_id', $address->city_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('client.addresses.edit', compact('address', 'cities', 'areas'));
    }

    public function update(Request $request, ClientAddress $address)
    {
        $client = Auth::guard('client')->user();
        
        // Ensure the address belongs to the authenticated client
        if ($address->client_id !== $client->id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        // Build location JSON if lat/lng exist
        if (isset($validated['lat'], $validated['lng'])) {
            $validated['location'] = [
                'lat' => (float) $validated['lat'],
                'lng' => (float) $validated['lng'],
            ];
        }

        // Remove lat/lng from validated to avoid mass assignment issues
        unset($validated['lat'], $validated['lng']);

        // Update the address
        $address->update($validated);

        return redirect()->route('client.addresses.index')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم تحديث العنوان بنجاح' 
                : 'Address updated successfully');
    }

    public function destroy(ClientAddress $address)
    {
        $client = Auth::guard('client')->user();
        
        // Ensure the address belongs to the authenticated client
        if ($address->client_id !== $client->id) {
            abort(403);
        }
        
        $address->delete();

        return redirect()->route('client.addresses.index')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم حذف العنوان بنجاح' 
                : 'Address deleted successfully');
    }
}

