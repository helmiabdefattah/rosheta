<?php

namespace App\Support;

use App\Models\Setting;

/**
 * "Try it free" — the invitation shown on the PRODUCTION site that sends a
 * visitor to the demo deployment.
 *
 * The demo sandbox runs as its own installation with DEMO_ENABLED=true and its
 * own database. Production does not host it, so on production the invitation
 * is an outbound link, not a form: an administrator turns it on and sets the
 * address in the admin panel (Settings → Try it free), which is why this is a
 * setting row rather than an env var — the URL changes without a deploy.
 *
 * Inside the demo installation itself the link is hidden: the visitor is
 * already there, and the real invitation is the start card.
 */
class DemoInvite
{
    public const ENABLED_KEY = 'demo_invite.enabled';

    public const URL_KEY = 'demo_invite.url';

    /** Show the button? Off unless switched on AND given somewhere to go. */
    public static function enabled(): bool
    {
        return ! config('demo.enabled')
            && Setting::getBool(self::ENABLED_KEY)
            && static::url() !== null;
    }

    /** The demo installation's address, or null if none is configured. */
    public static function url(): ?string
    {
        $url = trim((string) Setting::get(self::URL_KEY, ''));

        return $url === '' ? null : $url;
    }
}
