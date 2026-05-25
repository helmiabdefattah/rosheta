<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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
     * API login mirrors web POST /login: try staff User first, then Client; issue Sanctum token for whichever matches.
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

        $staffUser = $this->findUserByLogin($login);
        if ($staffUser && Hash::check($password, $staffUser->password)) {
            $this->applyOptionalFcm($staffUser, $request);

            $token = $staffUser->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'account_type' => 'staff',
                'user' => $this->serializeStaffUser($staffUser),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }

        $client = $this->findClientByLogin($login);

        if (! $client || ! Hash::check($password, $client->password)) {
            throw ValidationException::withMessages([
                'login' => [__('The provided credentials are incorrect.')],
            ]);
        }

        $this->applyOptionalFcm($client, $request);

        $token = $client->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'account_type' => 'client',
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
     * Logout (revoke current Sanctum token) for staff User or Client.
     */
    public function logout(Request $request)
    {
        $auth = $request->user();
        if ($auth !== null) {
            $token = $auth->currentAccessToken();
            if ($token !== null) {
                $token->delete();
            }
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Verify Sanctum token (staff or client).
     */
    public function verify(Request $request)
    {
        $auth = $request->user();

        if (! $auth) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired token',
            ], 401);
        }

        if ($auth instanceof User) {
            return response()->json([
                'valid' => true,
                'message' => 'Token is valid',
                'account_type' => 'staff',
                'user' => $this->serializeStaffUser($auth),
            ]);
        }

        if ($auth instanceof Client) {
            return response()->json([
                'valid' => true,
                'message' => 'Token is valid',
                'account_type' => 'client',
                'client' => [
                    'id' => $auth->id,
                    'name' => $auth->name,
                    'phone_number' => $auth->phone_number,
                    'email' => $auth->email,
                ],
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Invalid or expired token',
        ], 401);
    }

    /**
     * Current account profile (staff User or Client).
     */
    public function me(Request $request)
    {
        $auth = $request->user();

        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($auth instanceof User) {
            return response()->json([
                'account_type' => 'staff',
                'user' => $this->serializeStaffUser($auth),
                'client' => null,
                'addresses' => [],
                'addresses_count' => 0,
            ]);
        }

        if ($auth instanceof Client) {
            $addresses = ClientAddress::where('client_id', $auth->id)
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
                        'created_at' => optional($address->created_at)->toISOString(),
                        'updated_at' => optional($address->updated_at)->toISOString(),
                    ];
                });

            return response()->json([
                'account_type' => 'client',
                'user' => null,
                'client' => [
                    'id' => $auth->id,
                    'name' => $auth->name,
                    'phone_number' => $auth->phone_number,
                    'email' => $auth->email,
                    'avatar' => $auth->avatar,
                    'created_at' => optional($auth->created_at)->toISOString(),
                ],
                'addresses' => $addresses,
                'addresses_count' => $addresses->count(),
            ]);
        }

        return response()->json(['message' => 'Unauthenticated'], 401);
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

    private function applyOptionalFcm(Model $model, Request $request): void
    {
        if (! $request->filled('fcm_token') || ! $request->filled('platform')) {
            return;
        }

        if ($request->platform === 'web') {
            $model->fcm_token_web = $request->input('fcm_token');
        } else {
            $model->fcm_token_mobile = $request->input('fcm_token');
        }

        $model->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeStaffUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'laboratory_id' => $user->laboratory_id,
            'pharmacy_id' => $user->pharmacy_id,
            'nurse_id' => $user->nurse_id,
            'is_doctor' => $user->doctor()->exists(),
        ];
    }
}
