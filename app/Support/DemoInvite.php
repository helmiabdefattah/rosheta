<?php

namespace App\Support;

use App\Models\Setting;

/**
 * "Try it free" — the invitation shown on the PRODUCTION site that sends a
 * visitor to the demo deployment.
 *
 * The demo sandbox runs as its own installation with DEMO_ENABLED=true and its
 * own database. Production does not host it, so on production the invitation
 * is an outbound link, not a form: the address lives in the admin panel
 * (Settings → Try it free) rather than in an env var, so the sandbox can move
 * or go quiet without a deploy.
 *
 * Inside the demo installation itself the link is hidden: the visitor is
 * already there, and the real invitation is the start card.
 *
 * Both halves have a default, so a production database with no settings rows
 * yet still shows the invitation pointing at the sandbox we actually run. An
 * administrator overrides either half at any time; nothing here needs a
 * deploy.
 */
class DemoInvite
{
    public const ENABLED_KEY = 'demo_invite.enabled';

    public const URL_KEY = 'demo_invite.url';

    /** Where the invitation points until an administrator says otherwise. */
    public const DEFAULT_URL = 'https://dev.mostashfaon.com';

    /**
     * Show the button? On unless an administrator has switched it off — and
     * never inside the demo installation itself, where it would point at the
     * page the visitor is already on.
     */
    public static function enabled(): bool
    {
        return ! config('demo.enabled')
            && Setting::getBool(self::ENABLED_KEY, true);
    }

    /** The demo installation's address: the configured one, or the default. */
    public static function url(): string
    {
        $url = trim((string) Setting::get(self::URL_KEY, ''));

        return $url === '' ? self::DEFAULT_URL : $url;
    }
}
