<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'client_id' => 'required|exists:clients,id'
            ]);

            $clientId = $request->client_id;

            $addresses = ClientAddress::where('client_id', $clientId)
                ->with(['city', 'area'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $addresses,
                'message' => 'Addresses retrieved successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve addresses.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Add new client address
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'address'   => 'required|string|max:255',
            'city_id'   => 'required|exists:cities,id',
            'area_id'   => 'required|exists:areas,id',
            'lat'       => 'nullable|numeric',
            'lng'       => 'nullable|numeric',
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
            'location' => $location,
        ]);

        return response()->json([
            'message' => 'Address added successfully',
            'data' => $address,
        ], 201);
    }

    /**
     * Update client address
     */
    public function update(Request $request, $id)
    {
        $address = ClientAddress::findOrFail($id);

        $validated = $request->validate([
            'address' => 'sometimes|required|string|max:255',
            'city_id' => 'sometimes|required|exists:cities,id',
            'area_id' => 'sometimes|required|exists:areas,id',
            'lat'     => 'nullable|numeric',
            'lng'     => 'nullable|numeric',
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

        return response()->json([
            'message' => 'Address updated successfully',
            'data' => $address
        ]);
    }

    /**
     * Delete client address
     */
    public function destroy($id)
    {
        $address = ClientAddress::findOrFail($id);
        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully'
        ], 200);
    }
}
