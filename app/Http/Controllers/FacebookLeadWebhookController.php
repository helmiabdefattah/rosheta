<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Facebook Lead Ads → CRM webhook.
 *
 * This is the URL you paste into a Lead Ad's "Connect your CRM" / webhook
 * integration. Facebook first GETs it to verify the callback (echoing back the
 * challenge when the verify token matches), then POSTs a small notification for
 * every new lead. The notification only carries a `leadgen_id`; the actual
 * answers (name, phone, ...) are pulled from the Graph API with a page access
 * token. Every lead is stored as a SubscriptionRequest with source=facebook so
 * it lands in the same admin "Subscription Requests" report as the website
 * form — even when no page token is configured (as a stub to follow up).
 */
class FacebookLeadWebhookController extends Controller
{
    /** Facebook callback verification (GET). */
    public function verify(Request $request)
    {
        $verifyToken = config('services.facebook_leads.verify_token');

        if (
            $request->query('hub_mode') === 'subscribe'
            && $verifyToken
            && hash_equals((string) $verifyToken, (string) $request->query('hub_verify_token'))
        ) {
            return response($request->query('hub_challenge'), 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /** Incoming lead notifications (POST). */
    public function handle(Request $request)
    {
        // Optional but recommended: verify the payload signature so only
        // Facebook (holding your app secret) can post leads here.
        if (! $this->signatureValid($request)) {
            return response('Invalid signature', 403);
        }

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? null) !== 'leadgen') {
                    continue;
                }

                $value = $change['value'] ?? [];
                $leadgenId = $value['leadgen_id'] ?? null;

                if (! $leadgenId || SubscriptionRequest::where('external_ref', $leadgenId)->exists()) {
                    continue; // no id, or already stored (Facebook retries).
                }

                $this->storeLead($leadgenId, $this->fetchLeadFields($leadgenId), $value);
            }
        }

        // Always 200 quickly, or Facebook keeps retrying.
        return response('EVENT_RECEIVED', 200);
    }

    /** Pull the lead's field data from the Graph API, if a token is set. */
    private function fetchLeadFields(string $leadgenId): array
    {
        $token = config('services.facebook_leads.page_access_token');
        if (! $token) {
            return [];
        }

        try {
            $version = config('services.facebook_leads.graph_version', 'v21.0');
            $response = Http::get("https://graph.facebook.com/{$version}/{$leadgenId}", [
                'fields' => 'field_data',
                'access_token' => $token,
            ]);

            if (! $response->successful()) {
                Log::warning('FB lead fetch failed', ['lead' => $leadgenId, 'body' => $response->body()]);
                return [];
            }

            $fields = [];
            foreach ($response->json('field_data', []) as $f) {
                $fields[$f['name']] = $f['values'][0] ?? null;
            }

            return $fields;
        } catch (\Throwable $e) {
            Log::warning('FB lead fetch error', ['lead' => $leadgenId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /** Map a lead into a SubscriptionRequest. */
    private function storeLead(string $leadgenId, array $fields, array $value): void
    {
        // Facebook field names vary by form; try the common ones.
        $get = fn (array $keys) => collect($keys)
            ->map(fn ($k) => $fields[$k] ?? null)
            ->first(fn ($v) => filled($v));

        $name = $get(['full_name', 'name', 'الاسم', 'first_name']) ?: 'Facebook lead';
        $phone = $get(['phone_number', 'phone', 'رقم_الهاتف', 'رقم_الجوال', 'mobile']);
        $email = $get(['email', 'البريد_الإلكتروني']);

        SubscriptionRequest::create([
            'source' => 'facebook',
            'external_ref' => $leadgenId,
            'contact_name' => $name,
            'contact_phone' => $phone ?: '—',
            'contact_email' => $email,
            'doctor_name' => $name,
            'notes' => $fields
                ? "Facebook Lead Ad\n" . collect($fields)->map(fn ($v, $k) => "{$k}: {$v}")->implode("\n")
                : "Facebook Lead Ad (id {$leadgenId}). Configure FB_LEADS_PAGE_TOKEN to pull full details.",
            'status' => SubscriptionRequest::STATUS_NEW,
        ]);
    }

    /** X-Hub-Signature-256 check (skipped when no app secret is configured). */
    private function signatureValid(Request $request): bool
    {
        $secret = config('services.facebook_leads.app_secret');
        if (! $secret) {
            return true; // Not configured: accept (verify token still gates setup).
        }

        $header = $request->header('X-Hub-Signature-256');
        if (! $header || ! str_starts_with($header, 'sha256=')) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $header);
    }
}
