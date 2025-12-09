<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new client
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:clients,phone_number',
            'email' => 'nullable|email|unique:clients,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $client = Client::create([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $token = $client->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Client registered successfully',
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'phone_number' => $client->phone_number,
                'email' => $client->email,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login client
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $client = Client::where('phone_number', $request->phone_number)->first();

        if (!$client || !Hash::check($request->password, $client->password)) {
            throw ValidationException::withMessages([
                'phone_number' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing tokens (optional - for single device login)
        // $client->tokens()->delete();

        $token = $client->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'phone_number' => $client->phone_number,
                'email' => $client->email,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout client (revoke current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated client
     */
    public function me(Request $request)
    {
        $user = $request->user();

        $addresses = ClientAddress::where('client_id', $user->id)
            ->with(['city', 'area'])
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'address' => $address->address,
                    'location' => $address->location,
                    'city' => $address->city ? [
                        'id' => $address->city->id,
                        'name' => $address->city->name,
                        'name_ar' => $address->city->name_ar,
                    ] : null,
                    'area' => $address->area ? [
                        'id' => $address->area->id,
                        'name' => $address->area->name,
                        'name_ar' => $address->area->name_ar,
                    ] : null,
                    'created_at' => $address->created_at?->toISOString(),
                    'updated_at' => $address->updated_at?->toISOString(),
                ];
            });

        return response()->json([
            'client' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at?->toISOString(),
            ],
            'addresses' => $addresses,
            'addresses_count' => $addresses->count(),
        ]);
    }
}
