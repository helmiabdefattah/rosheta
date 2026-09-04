<?php

namespace App\Http\Controllers;

use App\Demo\DemoContext;
use App\Demo\DemoPurger;
use App\Demo\DemoSeeder;
use App\Models\DemoSession;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * The demo sandbox entry points.
 *
 * By the time any of these run, StartDemoSession has already pointed the
 * request at the demo database (every route here lives under /demo/*), so
 * ordinary Eloquent writes land in the demo database — except DemoSession,
 * which is pinned to production because it is a marketing record that must
 * outlive the tenant.
 */
class DemoController extends Controller
{
    public function __construct(
        private readonly DemoSeeder $seeder,
        private readonly DemoPurger $purger,
        private readonly DemoContext $context,
    ) {
    }

    /**
     * Create a fully populated clinic and log the visitor into it.
     *
     * Idempotent: reloading with a live demo cookie returns to the existing
     * workspace instead of building a second tenant.
     */
    public function start(Request $request): RedirectResponse
    {
        if (! config('demo.enabled')) {
            return redirect()->route('login')->withErrors([
                'email' => __('التجربة متوقفة مؤقتاً. برجاء المحاولة لاحقاً.'),
            ]);
        }

        $role = $request->input('role') === 'assistant' ? 'assistant' : 'doctor';

        if ($existing = $this->activeSession($request)) {
            return $this->enterWorkspace($existing, $role);
        }

        if ($problem = $this->rejectIfOverLimit($request)) {
            return $problem;
        }

        $session = DemoSession::create([
            'started_role' => $role,
            'template_key' => 'general_v1',
            'specialty' => $request->input('specialty'),
            'started_at' => now(),
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes((int) config('demo.max_duration_minutes')),
            'steps_completed' => [],
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'device' => $request->userAgent() && str_contains($request->userAgent(), 'Mobile') ? 'mobile' : 'desktop',
        ] + $this->attribution($request));

        // The context is already "demo" (path based), but the seeder needs the
        // session id to stamp on the tenant.
        $this->context->activate($session->id);

        try {
            $seeded = $this->seeder->seed($session->id, $request->input('specialty'));
        } catch (\Throwable $e) {
            Log::error('Demo seeding failed', [
                'demo_session' => $session->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile().':'.$e->getLine(),
            ]);

            $session->forceFill(['ended_at' => now(), 'end_reason' => 'purged'])->save();
            $this->purger->purgeSession($session, 'purged');

            return redirect()->route('login')->withErrors([
                'email' => __('تعذّر تجهيز بيئة التجربة الآن. برجاء المحاولة بعد قليل.'),
            ]);
        }

        $session->forceFill([
            'doctor_id' => $seeded['doctor']->id,
            'doctor_user_id' => $seeded['doctor_user']->id,
            'assistant_user_id' => $seeded['assistant_user']->id,
        ])->save();

        return $this->enterWorkspace($session, $role);
    }

    /** Swap between the doctor and the assistant inside the same tenant. */
    public function switchRole(Request $request): RedirectResponse
    {
        $session = $this->activeSession($request);

        if (! $session) {
            return redirect()->route('demo.ended', ['reason' => 'expired']);
        }

        $role = Auth::id() === $session->assistant_user_id ? 'doctor' : 'assistant';

        return $this->enterWorkspace($session, $role);
    }

    /** Wipe the tenant and build a fresh one, keeping the same demo session. */
    public function reset(Request $request): RedirectResponse
    {
        $session = $this->activeSession($request);

        if (! $session) {
            return redirect()->route('demo.ended', ['reason' => 'expired']);
        }

        $role = Auth::id() === $session->assistant_user_id ? 'assistant' : 'doctor';

        $this->logoutDemoUser($request);
        $this->purger->purgeDoctor((int) $session->doctor_id);

        $seeded = $this->seeder->seed($session->id, $session->specialty);

        $session->forceFill([
            'doctor_id' => $seeded['doctor']->id,
            'doctor_user_id' => $seeded['doctor_user']->id,
            'assistant_user_id' => $seeded['assistant_user']->id,
            'expires_at' => now()->addMinutes((int) config('demo.max_duration_minutes')),
            'last_activity_at' => now(),
            'steps_completed' => [],
            'purged_at' => null,
        ])->save();

        return $this->enterWorkspace($session, $role);
    }

    /** End the demo on purpose: destroy every trace, then return to login. */
    public function end(Request $request): RedirectResponse
    {
        $session = $this->sessionFromCookie($request);

        if ($session) {
            $session->forceFill(['ended_at' => now(), 'end_reason' => 'user_ended'])->save();
            $this->logoutDemoUser($request);
            $this->purger->purgeSession($session, 'user_ended');
        }

        return redirect()->route('demo.ended', ['reason' => 'user_ended'])
            ->withCookie(Cookie::forget(config('demo.cookie')));
    }

    /** Explains why the workspace is gone, and offers another run. */
    public function ended(Request $request): View
    {
        return view('demo.ended', [
            'reason' => $request->query('reason', 'user_ended'),
        ]);
    }

    /** Countdown and progress for the demo bar. */
    public function status(Request $request)
    {
        $session = $this->activeSession($request);

        if (! $session) {
            return response()->json(['code' => 'DEMO_ENDED'], 401);
        }

        return response()->json([
            'session_id' => $session->id,
            'seconds_remaining' => $session->secondsRemaining(),
            'expires_at' => $session->expires_at?->toIso8601String(),
            'role' => Auth::id() === $session->assistant_user_id ? 'assistant' : 'doctor',
        ]);
    }

    // =====================================================================

    /** Log in as the requested role and land on that role's dashboard. */
    protected function enterWorkspace(DemoSession $session, string $role): RedirectResponse
    {
        $userId = $role === 'assistant' ? $session->assistant_user_id : $session->doctor_user_id;
        $user = User::find($userId);

        if (! $user) {
            Log::error('Demo session lost its user', ['demo_session' => $session->id, 'role' => $role]);

            return redirect()->route('demo.ended', ['reason' => 'purged']);
        }

        Auth::guard('web')->login($user);
        session()->regenerate();

        // The workspace is Arabic; the app defaults to English.
        session(['locale' => 'ar']);
        session([Doctor::ACTIVE_CLINIC_SESSION_KEY => $this->firstClinicId($session)]);

        $route = $role === 'assistant' ? 'practice.assistant.dashboard' : 'practice.doctor.dashboard';

        return redirect()->route($route)->withCookie(
            cookie(
                name: config('demo.cookie'),
                value: DemoContext::cookieValue($session->id),
                minutes: (int) config('demo.max_duration_minutes'),
                httpOnly: true,
                sameSite: 'lax',
            )
        );
    }

    protected function firstClinicId(DemoSession $session): ?int
    {
        return Doctor::find($session->doctor_id)?->clinics()->value('id');
    }

    /** The session behind the cookie, whatever its state. */
    protected function sessionFromCookie(Request $request): ?DemoSession
    {
        $id = DemoContext::readCookie($request);

        return $id ? DemoSession::find($id) : null;
    }

    /** The session behind the cookie, only if it is still usable. */
    protected function activeSession(Request $request): ?DemoSession
    {
        $session = $this->sessionFromCookie($request);

        return $session && $session->isActive() && $session->doctor_id ? $session : null;
    }

    protected function logoutDemoUser(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /** Cheap abuse limits: per IP and overall. */
    protected function rejectIfOverLimit(Request $request): ?RedirectResponse
    {
        $ipHash = hash('sha256', (string) $request->ip());

        $activeGlobally = DemoSession::whereNull('ended_at')->where('expires_at', '>', now())->count();

        if ($activeGlobally >= (int) config('demo.global_max_active')) {
            return redirect()->route('login')->withErrors([
                'email' => __('التجربة مزدحمة حالياً. برجاء المحاولة بعد قليل.'),
            ]);
        }

        $startedToday = DemoSession::where('ip_hash', $ipHash)
            ->where('created_at', '>=', now()->startOfDay())->count();

        if ($startedToday >= (int) config('demo.max_starts_per_ip_per_day')) {
            return redirect()->route('login')->withErrors([
                'email' => __('لقد بدأت عدداً كبيراً من التجارب اليوم. برجاء المحاولة غداً.'),
            ]);
        }

        $concurrent = DemoSession::where('ip_hash', $ipHash)
            ->whereNull('ended_at')->where('expires_at', '>', now())->count();

        if ($concurrent >= (int) config('demo.max_concurrent_per_ip')) {
            return redirect()->route('login')->withErrors([
                'email' => __('لديك تجارب مفتوحة بالفعل. أنهِ إحداها ثم حاول مرة أخرى.'),
            ]);
        }

        return null;
    }

    /** UTM + click ids, for attributing paid traffic to conversions. */
    protected function attribution(Request $request): array
    {
        return [
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'utm_content' => $request->input('utm_content'),
            'utm_term' => $request->input('utm_term'),
            'fbclid' => $request->input('fbclid'),
            'gclid' => $request->input('gclid'),
            'ttclid' => $request->input('ttclid'),
        ];
    }
}
