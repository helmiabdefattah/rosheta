<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\User;
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
     * Login client (Sanctum). Matches web order: staff User is checked first; this API only
     * issues tokens for Client (patients). Lookup uses email OR phone with case-insensitive email
     * and a digits-only phone fallback.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string',
            'platform' => 'nullable|in:web,mobile',
        ]);

        $login = null;
        if ($request->filled('login')) {
            $login = trim((string) $request->input('login'));
        } elseif ($request->filled('email')) {
            $login = trim((string) $request->input('email'));
        } elseif ($request->filled('phone_number')) {
            $login = trim((string) $request->input('phone_number'));
        }

        if ($login === null || $login === '') {
            throw ValidationException::withMessages([
                'login' => [__('The email or phone number field is required.')],
            ]);
        }

        $password = (string) $request->input('password');

        $user = $this->findUserByLogin($login);
        if ($user && Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('The mobile app uses a patient account. Staff and partners sign in on the website with this email or phone.')],
            ]);
        }

        $client = $this->findClientByLogin($login);

        if (! $client || ! Hash::check($password, $client->password)) {
            throw ValidationException::withMessages([
                'login' => [__('The provided credentials are incorrect.')],
            ]);
        }

        // Update FCM token if provided
        if ($request->filled('fcm_token') && $request->filled('platform')) {
            if ($request->platform === 'web') {
                $client->fcm_token_web = $request->fcm_token;
            } else {
                $client->fcm_token_mobile = $request->fcm_token;
            }
            $client->save();
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
     * Verify authentication token
     */
    public function verify(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired token',
            ], 401);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Token is valid',
            'client' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
            ],
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

    /**
     * Same identifier rules as web login for users (email, case-insensitive email, phone, digits-only phone).
     */
    private function findUserByLogin(string $login): ?User
    {
        return User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhereRaw('LOWER(email) = ?', [mb_strtolower($login, 'UTF-8')])
                ->orWhere('phone_number', $login);

            if (! str_contains($login, '@')) {
                $digits = preg_replace('/\D/', '', $login);
                if (strlen($digits) >= 8) {
                    $query->orWhere('phone_number', $digits);
                }
            }
        })->first();
    }

    /**
     * Same identifier rules as web login for clients.
     */
    private function findClientByLogin(string $login): ?Client
    {
        return Client::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhereRaw('LOWER(email) = ?', [mb_strtolower($login, 'UTF-8')])
                ->orWhere('phone_number', $login);

            if (! str_contains($login, '@')) {
                $digits = preg_replace('/\D/', '', $login);
                if (strlen($digits) >= 8) {
                    $query->orWhere('phone_number', $digits);
                }
            }
        })->first();
    }
}
