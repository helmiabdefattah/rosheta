<?php

namespace App\Http\Middleware;

use App\Demo\DemoContext;
use App\Models\DemoSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Points a demo request at the demo database — and at its own session store,
 * cache prefix and queue — before anything else in the web group runs.
 *
 * ORDER IS THE WHOLE POINT. This middleware is prepended to the `web` group
 * (bootstrap/app.php) so it executes ahead of StartSession and Authenticate.
 * If it ran later, Laravel would already have opened the session and resolved
 * the user against the PRODUCTION database, and the demo login would either
 * fail or — far worse — read real data.
 *
 * That is also why DemoContext decides from the request path or a dedicated
 * signed cookie and never from the session: at this point there is no session.
 */
class StartDemoSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(DemoContext::class);

        if (! $context->resolveFrom($request)) {
            return $next($request);
        }

        $this->pointInfrastructureAtDemo();
        $context->markSwitched();

        // POST /demo/start has no session row yet; it creates one.
        $sessionId = $context->sessionId();

        if ($sessionId === null) {
            return $next($request);
        }

        $demoSession = DemoSession::find($sessionId);

        if ($demoSession === null || ! $demoSession->isActive()) {
            return $this->rejectEndedSession($request, $demoSession);
        }

        $context->setDoctorId($demoSession->doctor_id);

        $this->touch($demoSession);

        return $next($request);
    }

    /**
     * Redirect the whole request stack — database, session store, cache and
     * queue — to demo infrastructure for the remainder of this request.
     */
    protected function pointInfrastructureAtDemo(): void
    {
        $demo = config('demo.connection');

        config([
            // Every model without an explicit $connection now resolves here.
            'database.default' => $demo,

            // Session rows follow the switch, and get their own cookie so a
            // demo session can never be mistaken for a production one.
            'session.connection' => $demo,
            'session.cookie' => config('demo.session_cookie'),

            // Cache and queue likewise, so demo entries never mix in.
            'cache.prefix' => config('demo.cache_prefix'),
            'cache.stores.database.connection' => $demo,
            'queue.connections.database.connection' => $demo,
            'queue.connections.database.queue' => config('demo.queue'),
            'queue.failed.database' => $demo,
            'queue.batching.database' => $demo,
        ]);

        // The cache manager memoises resolved stores, so anything resolved
        // before this point would still hold the production connection. Drop
        // it and let the next call rebuild against the demo config.
        if (app()->resolved('cache')) {
            app('cache')->forgetDriver(config('cache.default'));
        }

        // Telescope pins itself to the production connection in its own config,
        // so a recorded demo request would try to INSERT into production
        // telescope_entries and trip the write guard. Stop recording AND drop
        // whatever was queued during bootstrap, before the batch is flushed on
        // terminate. A public demo has no business filling the audit table.
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
            \Laravel\Telescope\Telescope::flushEntries();
        }
    }

    /**
     * Keep demo_sessions.last_activity_at fresh for the idle timeout, without
     * writing on every single request.
     */
    protected function touch(DemoSession $demoSession): void
    {
        if ($demoSession->last_activity_at !== null
            && $demoSession->last_activity_at->diffInSeconds(now()) < 60) {
            return;
        }

        $demoSession->forceFill(['last_activity_at' => now()])->save();
    }

    /**
     * The demo is over (ended, expired or idle). Close it down, drop the
     * cookie and send the visitor somewhere that explains why.
     */
    protected function rejectEndedSession(Request $request, ?DemoSession $demoSession): Response
    {
        if ($demoSession !== null && $demoSession->ended_at === null) {
            $demoSession->forceFill([
                'ended_at' => now(),
                'end_reason' => $demoSession->expiryReason() ?? 'expired',
            ])->save();
        }

        $reason = $demoSession?->end_reason ?? 'expired';

        if ($request->expectsJson()) {
            return response()->json([
                'code' => 'DEMO_ENDED',
                'reason' => $reason,
            ], 401)->withoutCookie(config('demo.cookie'));
        }

        return redirect()
            ->route('demo.ended', ['reason' => $reason])
            ->withoutCookie(config('demo.cookie'));
    }
}
