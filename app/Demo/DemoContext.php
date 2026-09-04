<?php

namespace App\Demo;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Request-scoped answer to "are we inside a demo?".
 *
 * Resolved from the request PATH or a dedicated signed cookie — never from the
 * session, because the connection switch has to happen before the session
 * store boots (otherwise Laravel would look the demo session up in the
 * production database). Registered as a singleton, so anything in the request
 * can ask it without re-deriving.
 */
class DemoContext
{
    /** Marks the request as belonging to an active demo. */
    protected bool $active = false;

    /** demo_sessions.id (uuid) of the running demo. */
    protected ?string $sessionId = null;

    /** doctors.id of the demo tenant, once the session has been looked up. */
    protected ?int $doctorId = null;

    /** True once the connection/session/cache switch has been applied. */
    protected bool $switched = false;

    public function isDemo(): bool
    {
        return $this->active;
    }

    public function sessionId(): ?string
    {
        return $this->sessionId;
    }

    public function doctorId(): ?int
    {
        return $this->doctorId;
    }

    public function hasSwitched(): bool
    {
        return $this->switched;
    }

    public function markSwitched(): void
    {
        $this->switched = true;
    }

    public function activate(?string $sessionId = null): void
    {
        $this->active = true;
        $this->sessionId = $sessionId;
    }

    public function setDoctorId(?int $doctorId): void
    {
        $this->doctorId = $doctorId;
    }

    /**
     * Whether this request should run against the demo database, decided
     * without touching the session:
     *
     *  - any /demo/* route (covers "start", before a cookie exists), or
     *  - a valid signed demo cookie (covers the whole workspace afterwards).
     */
    public function resolveFrom(Request $request): bool
    {
        if (! config('demo.enabled')) {
            return false;
        }

        if ($request->is('demo', 'demo/*')) {
            $this->activate(static::readCookie($request));

            return true;
        }

        $sessionId = static::readCookie($request);

        if ($sessionId !== null) {
            $this->activate($sessionId);

            return true;
        }

        return false;
    }

    /**
     * The demo cookie is "{uuid}.{hmac}" signed with the app key. It is
     * excluded from Laravel's cookie encryption (see bootstrap/app.php) so it
     * can be read before EncryptCookies runs.
     */
    public static function readCookie(Request $request): ?string
    {
        $raw = $request->cookies->get(config('demo.cookie'));

        if (! is_string($raw) || ! str_contains($raw, '.')) {
            return null;
        }

        [$id, $signature] = explode('.', $raw, 2);

        if (! Str::isUuid($id) || ! hash_equals(static::sign($id), $signature)) {
            return null;
        }

        return $id;
    }

    /** Cookie value for a session id, signed so it cannot be forged. */
    public static function cookieValue(string $sessionId): string
    {
        return $sessionId.'.'.static::sign($sessionId);
    }

    protected static function sign(string $sessionId): string
    {
        return hash_hmac('sha256', 'demo|'.$sessionId, (string) config('app.key'));
    }
}
