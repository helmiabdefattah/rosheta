<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionRequest;
use Illuminate\Http\Request;

/**
 * Public "subscribe to a package" flow reached from the pricing page.
 *
 * It does NOT create any account: it captures the doctor / clinic / assistant
 * details and a contact number, stores them for the team, and promises a call
 * back within 24h. The accounts themselves are created by an admin afterwards.
 */
class SubscribeController extends Controller
{
    /** Valid plan keys mirrored from the pricing page. */
    private const PLANS = ['starter', 'equipped', 'multi'];

    public function create(Request $request)
    {
        $plan = in_array($request->query('plan'), self::PLANS, true)
            ? $request->query('plan')
            : null;

        $billing = $request->query('billing') === 'annual' ? 'annual' : 'monthly';
        $withProfileSite = $request->query('addon') === 'profile';

        return view('subscribe.create', compact('plan', 'billing', 'withProfileSite'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'nullable|in:starter,equipped,multi',
            'billing' => 'nullable|in:monthly,annual',
            'with_profile_site' => 'nullable|boolean',
            'profile_subdomain' => 'nullable|string|max:100',

            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
            'contact_email' => 'nullable|email|max:255',

            'doctor_name' => 'required|string|max:255',
            'doctor_specialty' => 'nullable|string|max:255',
            'doctor_username' => 'nullable|string|max:255',
            'doctor_phone' => 'nullable|string|max:50',
            'doctor_email' => 'nullable|email|max:255',
            'doctor_password' => 'nullable|string|max:255',

            'clinic_name' => 'nullable|string|max:255',
            'clinic_address' => 'nullable|string|max:1000',
            'clinic_city' => 'nullable|string|max:255',
            'clinic_phone' => 'nullable|string|max:50',

            'assistants' => 'nullable|array|max:12',
            'assistants.*.name' => 'nullable|string|max:255',
            'assistants.*.username' => 'nullable|string|max:255',
            'assistants.*.phone' => 'nullable|string|max:50',
            'assistants.*.password' => 'nullable|string|max:255',

            'notes' => 'nullable|string|max:5000',
        ]);

        // Keep only assistant rows the visitor actually filled in.
        $assistants = collect($request->input('assistants', []))
            ->map(fn ($a) => [
                'name' => trim($a['name'] ?? ''),
                'username' => trim($a['username'] ?? ''),
                'phone' => trim($a['phone'] ?? ''),
                'password' => (string) ($a['password'] ?? ''),
            ])
            ->filter(fn ($a) => $a['name'] !== '' || $a['username'] !== '' || $a['phone'] !== '')
            ->values()
            ->all();

        SubscriptionRequest::create([
            'plan' => $validated['plan'] ?? null,
            'billing' => $validated['billing'] ?? 'monthly',
            'with_profile_site' => $request->boolean('with_profile_site'),
            'profile_subdomain' => $validated['profile_subdomain'] ?? null,

            'contact_name' => $validated['contact_name'],
            'contact_phone' => $validated['contact_phone'],
            'contact_email' => $validated['contact_email'] ?? null,

            'doctor_name' => $validated['doctor_name'],
            'doctor_specialty' => $validated['doctor_specialty'] ?? null,
            'doctor_username' => $validated['doctor_username'] ?? null,
            'doctor_phone' => $validated['doctor_phone'] ?? null,
            'doctor_email' => $validated['doctor_email'] ?? null,
            'doctor_password' => ($validated['doctor_password'] ?? '') !== '' ? $validated['doctor_password'] : null,

            'clinic_name' => $validated['clinic_name'] ?? null,
            'clinic_address' => $validated['clinic_address'] ?? null,
            'clinic_city' => $validated['clinic_city'] ?? null,
            'clinic_phone' => $validated['clinic_phone'] ?? null,

            'assistants' => $assistants ?: null,
            'notes' => $validated['notes'] ?? null,

            'status' => SubscriptionRequest::STATUS_NEW,
            'locale' => app()->getLocale(),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('subscribe.thanks');
    }

    public function thanks()
    {
        return view('subscribe.thanks');
    }
}
