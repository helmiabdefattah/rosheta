<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class WebviewBridgeController extends Controller
{
    /**
     * Issue a one-time signed URL for the mobile app WebView to establish a web session
     * (client guard or web guard for staff), matching post-login redirects on the website.
     */
    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'redirect' => 'nullable|string|max:500',
        ]);

        $auth = $request->user();

        if ($auth instanceof User) {
            $redirect = $this->sanitizeStaffRedirect($validated['redirect'] ?? null, $auth);
            $nonce = Str::random(64);
            Cache::put(
                'webview_bridge:'.$nonce,
                [
                    'type' => 'staff',
                    'user_id' => $auth->id,
                    'redirect' => $redirect,
                ],
                now()->addMinutes(5)
            );

            return response()->json([
                'url' => URL::temporarySignedRoute(
                    'webview.bridge.establish',
                    now()->addMinutes(5),
                    ['nonce' => $nonce]
                ),
                'expires_in' => 300,
            ]);
        }

        if ($auth instanceof Client) {
            $redirect = $this->sanitizeClientRedirect($validated['redirect'] ?? null);
            $nonce = Str::random(64);
            Cache::put(
                'webview_bridge:'.$nonce,
                [
                    'type' => 'client',
                    'client_id' => $auth->id,
                    'redirect' => $redirect,
                ],
                now()->addMinutes(5)
            );

            return response()->json([
                'url' => URL::temporarySignedRoute(
                    'webview.bridge.establish',
                    now()->addMinutes(5),
                    ['nonce' => $nonce]
                ),
                'expires_in' => 300,
            ]);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    /**
     * Establish web session from signed URL (client or staff).
     */
    public function establish(Request $request): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired signature');
        }

        $nonce = $request->query('nonce');
        if (! is_string($nonce) || $nonce === '') {
            abort(400, 'Missing nonce');
        }

        $payload = Cache::pull('webview_bridge:'.$nonce);
        if (! is_array($payload)) {
            abort(403, 'Invalid or expired bridge token');
        }

        $type = $payload['type'] ?? 'client';

        if ($type === 'staff') {
            if (empty($payload['user_id'])) {
                abort(403, 'Invalid or expired bridge token');
            }

            $user = User::find($payload['user_id']);
            if (! $user) {
                abort(404, 'User not found');
            }

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            $redirect = $payload['redirect'] ?? $this->defaultStaffDashboardUrl($user);

            return redirect()->to($redirect);
        }

        if (empty($payload['client_id'])) {
            abort(403, 'Invalid or expired bridge token');
        }

        $client = Client::find($payload['client_id']);
        if (! $client) {
            abort(404, 'Client not found');
        }

        Auth::guard('client')->login($client);
        $request->session()->regenerate();

        $redirect = $payload['redirect'] ?? '/client/dashboard';

        return redirect()->to($redirect);
    }

    private function defaultStaffDashboardUrl(User $user): string
    {
        if ($user->laboratory_id) {
            return route('laboratories.dashboard');
        }
        if ($user->pharmacy_id) {
            return route('pharmacies.dashboard');
        }
        if ($user->nurse_id) {
            return route('nurse.dashboard');
        }
        if ($user->doctor()->exists()) {
            return route('doctor.dashboard');
        }

        return route('admin.dashboard');
    }

    private function sanitizeStaffRedirect(?string $path, User $user): string
    {
        $default = $this->defaultStaffDashboardUrl($user);

        if ($path === null || $path === '') {
            return $default;
        }

        $path = '/'.ltrim($path, '/');
        if (str_starts_with($path, '//')) {
            return $default;
        }

        $allowedPrefixes = ['/admin', '/laboratory', '/pharmacy', '/nurse', '/doctor', '/client'];

        foreach ($allowedPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $path;
            }
        }

        return $default;
    }

    private function sanitizeClientRedirect(?string $path): string
    {
        $default = '/client/dashboard';
        if ($path === null || $path === '') {
            return $default;
        }
        $path = '/'.ltrim($path, '/');
        if (str_starts_with($path, '//')) {
            return $default;
        }
        if (! str_starts_with($path, '/client')) {
            return $default;
        }

        return $path;
    }
}
