<?php

namespace App\Http\Controllers;

use App\Models\Client;
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
     * Issue a one-time signed URL for the mobile app WebView to establish a web session.
     * Called as POST /api/webview-bridge with Bearer token (Sanctum client).
     */
    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'redirect' => 'nullable|string|max:500',
        ]);

        $client = $request->user();
        if (!$client instanceof Client) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $redirect = $this->sanitizeRedirect($validated['redirect'] ?? null);

        $nonce = Str::random(64);
        Cache::put(
            'webview_bridge:'.$nonce,
            [
                'client_id' => $client->id,
                'redirect' => $redirect,
            ],
            now()->addMinutes(5)
        );

        $url = URL::temporarySignedRoute(
            'webview.bridge.establish',
            now()->addMinutes(5),
            ['nonce' => $nonce]
        );

        return response()->json([
            'url' => $url,
            'expires_in' => 300,
        ]);
    }

    /**
     * Establish client web session from signed URL (opened once in WebView).
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
        if (! is_array($payload) || empty($payload['client_id'])) {
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

    private function sanitizeRedirect(?string $path): string
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
