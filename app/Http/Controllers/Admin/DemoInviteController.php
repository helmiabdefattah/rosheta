<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\DemoInvite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The "Try it free" invitation on the public site.
 *
 * The demo sandbox is a separate deployment, so on production the invitation
 * is a link out to it. Both halves of that — whether to show it at all, and
 * where it points — are settings rather than env vars: the demo can be taken
 * down for an afternoon, or moved to another host, without a deploy.
 */
class DemoInviteController extends Controller
{
    public function edit(): View
    {
        return view('admin.demo-invite.edit', [
            // The stored intent, with the same defaults the public pages
            // fall back to — an administrator who has never touched this must
            // not read "off / blank" while the button is up. Deliberately not
            // DemoInvite::enabled(), which also answers "are we inside the
            // demo installation?" and would render the form permanently off
            // there.
            'enabled' => Setting::getBool(DemoInvite::ENABLED_KEY, true),
            'url' => DemoInvite::url(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $ar = app()->getLocale() === 'ar';

        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],

            // Required only when the button is being switched on: an
            // administrator turning it off should not have to invent a URL
            // first. http(s) only — this becomes an href on a public page.
            'url' => [
                'nullable',
                $request->boolean('enabled') ? 'required' : 'sometimes',
                'url',
                'starts_with:http://,https://',
                'max:255',
            ],
        ], [], [
            'url' => $ar ? 'رابط نظام التجربة' : 'demo system URL',
        ]);

        Setting::put(DemoInvite::URL_KEY, trim((string) ($data['url'] ?? '')));
        Setting::put(DemoInvite::ENABLED_KEY, $request->boolean('enabled') ? '1' : '0');

        return redirect()->route('admin.demo-invite.edit')
            ->with('success', $ar ? 'تم حفظ إعدادات التجربة' : 'Demo invitation settings saved');
    }
}
