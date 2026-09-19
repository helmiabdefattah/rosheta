<?php

namespace App\Demo;

use App\Models\DemoActivityEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Writes the journey of a demo run as it happens.
 *
 * Recorded live rather than reconstructed at the end, because the two ways a
 * run usually finishes — the visitor closes the tab, or the window expires —
 * never reach any code the visitor's browser is still talking to. By the time
 * demo:purge notices, the only thing left to ask is the database, and what was
 * clicked was never in it. So each request leaves its own trace instead.
 *
 * PRIVACY: route names, labels and route parameters. Never request payloads,
 * never what the visitor typed. The tenant that could identify them is deleted
 * minutes later; these rows are kept, so they must not carry anything personal.
 *
 * Failures here are swallowed on purpose. Analytics must never be the reason a
 * doctor's screen 500s.
 */
class DemoActivityRecorder
{
    /**
     * Actions fired repeatedly by the same intent (a typeahead, a refresh).
     * A second one inside the collapse window is the same act, not a new one.
     */
    private const NOISY_ACTIONS = [
        'practice.doctor.medicines.search',
    ];

    /** Seconds within which an identical page view or noisy action is dropped. */
    private const COLLAPSE_SECONDS = 20;

    /** Where the last event is remembered, in the DEMO session store. */
    private const LAST_KEY = 'demo_last_event';

    public function __construct(private readonly DemoContext $context)
    {
    }

    /**
     * Record a request, if it says anything about what the visitor wanted.
     */
    public function request(Request $request, Response $response): void
    {
        $sessionId = $this->context->sessionId();

        if ($sessionId === null || ! $this->context->isDemo()) {
            return;
        }

        $routeName = $request->route()?->getName();

        if ($this->isIgnored($request, $routeName)) {
            return;
        }

        // A failed write is not something they did — it is something that did
        // not happen. Redirects (302) and 200s both count; 4xx/5xx do not.
        $status = $response->getStatusCode();

        if ($status >= 400) {
            return;
        }

        $entry = DemoActivityCatalog::forRoute($routeName);
        $isRead = in_array($request->method(), ['GET', 'HEAD'], true);

        [$kind, $feature, $label, $labelEn] = $entry ?? [
            $isRead ? 'page' : 'action',
            $this->guessFeature($routeName, $request->path()),
            ...$this->guessLabels($routeName, $request),
        ];

        $action = $routeName ?: 'path:'.Str::limit($request->path(), 60, '');

        if ($this->shouldCollapse($request, $action, $isRead)) {
            return;
        }

        $this->write($sessionId, [
            'kind' => $kind,
            'action' => $action,
            'label' => $label,
            'label_en' => $labelEn,
            'method' => $request->method(),
            'route' => $routeName,
            'path' => Str::limit($request->path(), 180, ''),
            'status' => $status,
            'meta' => array_filter([
                'feature' => $feature,
                'params' => $this->routeParameters($request),
            ]),
        ]);

        $this->remember($request, $action);
    }

    /**
     * Record a milestone the controller knows about and no route implies:
     * the run starting, the clinic being built, a role switch, the ending.
     */
    public function milestone(string $action, ?string $sessionId = null, array $meta = []): void
    {
        $sessionId ??= $this->context->sessionId();

        if ($sessionId === null) {
            return;
        }

        $entry = DemoActivityCatalog::forSystem($action) ?? ['system', 'demo', $action, $action];

        $this->write($sessionId, [
            'kind' => $entry[0],
            'action' => $action,
            'label' => $entry[2],
            'label_en' => $entry[3],
            'method' => null,
            'route' => null,
            'path' => null,
            'status' => null,
            'meta' => array_filter(['feature' => $entry[1]] + $meta),
        ]);
    }

    /**
     * Close the trail with how the run ended, once.
     *
     * Two places notice an ending: the middleware, on the first request after
     * the window closed, and demo:purge, for a visitor who simply closed the
     * tab. Whichever gets there first writes it; the other must not write it
     * twice.
     */
    public function endingMilestone(string $sessionId, ?string $reason): void
    {
        $reason = in_array($reason, ['expired', 'idle', 'user_ended', 'converted', 'purged'], true)
            ? $reason
            : 'purged';

        try {
            $already = DemoActivityEvent::where('demo_session_id', $sessionId)
                ->where('action', 'like', 'demo.ended.%')
                ->exists();

            if ($already) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $this->milestone('demo.ended.'.$reason, $sessionId);
    }

    // =====================================================================

    protected function write(string $sessionId, array $attributes): void
    {
        try {
            DemoActivityEvent::create($attributes + [
                'demo_session_id' => $sessionId,
                'occurred_at' => now(),
                'role' => $this->role(),
            ]);
        } catch (\Throwable $e) {
            // Never break the visitor's screen over a metric.
            Log::warning('Demo activity not recorded', [
                'demo_session' => $sessionId,
                'action' => $attributes['action'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Which hat the visitor was wearing, decided after Authenticate ran. */
    protected function role(): ?string
    {
        $userId = Auth::id();

        if ($userId === null) {
            return null;
        }

        $assistantId = $this->context->assistantUserId();

        if ($assistantId === null) {
            return null;
        }

        return (int) $userId === (int) $assistantId ? 'assistant' : 'doctor';
    }

    protected function isIgnored(Request $request, ?string $routeName): bool
    {
        if ($routeName !== null && in_array($routeName, DemoActivityCatalog::IGNORED_ROUTES, true)) {
            return true;
        }

        if ($request->is(...DemoActivityCatalog::IGNORED_PATH_PATTERNS)) {
            return true;
        }

        // Prefetches and background pings are not visits.
        return $request->header('Purpose') === 'prefetch'
            || $request->header('Sec-Purpose') === 'prefetch';
    }

    /**
     * Drop a repeat of the same read within the collapse window: a refresh, a
     * back button, or a typeahead firing per keystroke. Kept in the demo
     * session store, which is already loaded — no extra query for a decision
     * taken on every request.
     */
    protected function shouldCollapse(Request $request, string $action, bool $isRead): bool
    {
        if (! $isRead && ! in_array($action, self::NOISY_ACTIONS, true)) {
            return false;
        }

        $last = $this->lastEvent($request);

        return is_array($last)
            && ($last['action'] ?? null) === $action
            && (time() - (int) ($last['at'] ?? 0)) < self::COLLAPSE_SECONDS;
    }

    protected function lastEvent(Request $request): ?array
    {
        try {
            return $request->hasSession() ? $request->session()->get(self::LAST_KEY) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function remember(Request $request, string $action): void
    {
        try {
            if ($request->hasSession()) {
                $request->session()->put(self::LAST_KEY, ['action' => $action, 'at' => time()]);
            }
        } catch (\Throwable) {
            // A session that is not there yet is not worth an exception.
        }
    }

    /**
     * Route parameters, ids only. Enough to see that three different patients
     * were opened rather than the same one three times, and nothing more.
     *
     * @return array<string, int|string>
     */
    protected function routeParameters(Request $request): array
    {
        $params = [];

        foreach ((array) $request->route()?->parameters() as $key => $value) {
            if (is_object($value)) {
                $value = method_exists($value, 'getKey') ? $value->getKey() : null;
            }

            if (is_int($value) || is_string($value)) {
                $params[$key] = is_string($value) ? Str::limit($value, 40, '') : $value;
            }
        }

        return $params;
    }

    /** A feature for a route nobody has catalogued yet. */
    protected function guessFeature(?string $routeName, string $path): string
    {
        $haystack = $routeName ?: $path;

        return match (true) {
            str_contains($haystack, 'prescription') => 'prescribing',
            str_contains($haystack, 'collection'), str_contains($haystack, 'item') => 'billing',
            str_contains($haystack, 'insurance') => 'insurance',
            str_contains($haystack, 'test'), str_contains($haystack, 'request') => 'requests',
            str_contains($haystack, 'patient'), str_contains($haystack, 'attachment') => 'records',
            str_contains($haystack, 'kiosk'), str_contains($haystack, 'assistant') => 'reception',
            str_contains($haystack, 'display'), str_contains($haystack, 'queue') => 'queue',
            str_contains($haystack, 'setup'), str_contains($haystack, 'clinic') => 'setup',
            str_contains($haystack, 'manager'), str_contains($haystack, 'report') => 'reports',
            str_contains($haystack, 'demo') => 'demo',
            default => 'examination',
        };
    }

    /**
     * A readable-ish label for an uncatalogued route, so a feature shipped
     * today still shows up in tomorrow's analysis.
     *
     * @return array{0:string,1:string}
     */
    protected function guessLabels(?string $routeName, Request $request): array
    {
        $verb = match ($request->method()) {
            'GET', 'HEAD' => ['فتح', 'Opened'],
            'DELETE' => ['حذف', 'Deleted'],
            'PUT', 'PATCH' => ['عدّل', 'Updated'],
            default => ['نفّذ', 'Performed'],
        };

        $what = Str::of($routeName ?: $request->path())
            ->replace(['practice.', 'demo.'], '')
            ->replace(['.', '-', '/'], ' ')
            ->trim()
            ->limit(80, '')
            ->value();

        return [$verb[0].' — '.$what, $verb[1].' — '.$what];
    }
}
