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
use Illuminate\Support\Str;
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
     * Open a demo: claim a session, then hand the visitor the loading page.
     *
     * The clinic itself is built by build() on the request that page sends,
     * not here. Seeding takes several seconds — long enough that doing it
     * inside this request means the visitor stares at a dead browser tab with
     * nothing to tell them anything is happening, and long enough that some of
     * them give up and never see the product at all.
     *
     * Idempotent: reloading with a live demo cookie returns to the existing
     * workspace instead of building a second tenant.
     */
    public function start(Request $request): RedirectResponse
    {
        if ($off = $this->rejectIfDisabled()) {
            return $off;
        }

        $role = $request->input('role') === 'assistant' ? 'assistant' : 'doctor';

        if ($existing = $this->activeSession($request)) {
            return $this->enterWorkspace($existing, $role);
        }

        // A session that was claimed but never built — the visitor closed the
        // loading page, or pressed the button twice. Reuse it instead of
        // leaving an orphan behind to count against their own limits.
        $session = $this->unbuiltSession($request);

        if ($session !== null) {
            $session->forceFill([
                'started_role' => $role,
                'specialty' => $request->input('specialty'),
                'last_activity_at' => now(),
                'expires_at' => now()->addMinutes((int) config('demo.max_duration_minutes')),
            ])->save();
        } else {
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
        }

        $this->rememberDoctorName($request);

        return redirect()->route('demo.preparing')->withCookie($this->demoCookie($session));
    }

    /**
     * The loading page: "we are preparing your journey".
     *
     * A GET of its own so a refresh re-renders it instead of re-posting, and
     * so reset() can send the visitor back to the same screen while their
     * clinic is rebuilt.
     */
    public function preparing(Request $request): View|RedirectResponse
    {
        if ($off = $this->rejectIfDisabled()) {
            return $off;
        }

        $session = $this->sessionFromCookie($request);

        if ($session === null || ! $session->isActive()) {
            return redirect()->route('login');
        }

        // Already built — a refresh after the build finished, or the back
        // button. Go straight in rather than showing a spinner for nothing.
        if ($session->doctor_id) {
            return $this->enterWorkspace($session, $this->roleOf($session));
        }

        return view('demo.preparing', [
            'role' => $this->roleOf($session),
            'doctorName' => $this->rememberedDoctorName(),
        ]);
    }

    /**
     * Build the clinic and log the visitor into it.
     *
     * Called by the loading page as soon as it has painted, so everything slow
     * happens while the visitor is watching something that explains itself.
     *
     * The loading page asks for this over fetch and navigates itself when the
     * answer arrives, so the answer has to be JSON for it — a form submit was
     * the obvious way to do it, but a pending cross-document navigation stops
     * the browser painting the very page whose whole job is to be on screen
     * for those seconds. The redirect is built either way, login and cookies
     * included, and only its target is repackaged.
     */
    public function build(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $response = $this->runBuild($request);

        if (! $request->expectsJson()) {
            return $response;
        }

        // Same response, described instead of followed: the cookies it sets
        // (demo tenant, regenerated session) still have to ride along, or the
        // page navigates as a stranger.
        $json = response()
            ->json(['redirect' => $response->getTargetUrl()])
            ->withHeaders(['Cache-Control' => 'no-store']);

        foreach ($response->headers->getCookies() as $cookie) {
            $json->withCookie($cookie);
        }

        return $json;
    }

    protected function runBuild(Request $request): RedirectResponse
    {
        if ($off = $this->rejectIfDisabled()) {
            return $off;
        }

        $session = $this->sessionFromCookie($request);

        if ($session === null || ! $session->isActive()) {
            return redirect()->route('demo.ended', ['reason' => 'expired']);
        }

        $role = $this->roleOf($session);

        // Double submit: the tenant is already there.
        if ($session->doctor_id) {
            return $this->enterWorkspace($session, $role);
        }

        // The context is already "demo" (cookie based), but the seeder needs
        // the session id to stamp on the tenant.
        $this->context->activate($session->id);

        try {
            $seeded = $this->seeder->seed(
                $session->id,
                $session->specialty,
                $this->rememberedDoctorName(),
            );
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
        if ($off = $this->rejectIfDisabled()) {
            return $off;
        }

        $session = $this->activeSession($request);

        if (! $session) {
            return redirect()->route('demo.ended', ['reason' => 'expired']);
        }

        $role = Auth::id() === $session->assistant_user_id ? 'doctor' : 'assistant';

        return $this->enterWorkspace($session, $role);
    }

    /**
     * Wipe the tenant and build a fresh one, keeping the same demo session.
     *
     * The rebuild is left to build(), reached through the same loading page as
     * the first one: it takes exactly as long, and a frozen dashboard is a
     * worse answer to "أعد التجربة" than a screen that says what it is doing.
     */
    public function reset(Request $request): RedirectResponse
    {
        if ($off = $this->rejectIfDisabled()) {
            return $off;
        }

        $session = $this->activeSession($request);

        if (! $session) {
            return redirect()->route('demo.ended', ['reason' => 'expired']);
        }

        $role = Auth::id() === $session->assistant_user_id ? 'assistant' : 'doctor';
        $doctorName = $this->rememberedDoctorName();

        $this->logoutDemoUser($request);
        $this->purger->purgeDoctor((int) $session->doctor_id);

        $session->forceFill([
            'doctor_id' => null,
            'doctor_user_id' => null,
            'assistant_user_id' => null,
            'started_role' => $role,
            'expires_at' => now()->addMinutes((int) config('demo.max_duration_minutes')),
            'last_activity_at' => now(),
            'steps_completed' => [],
            'purged_at' => null,
        ])->save();

        // logoutDemoUser() invalidated the session the name was living in.
        $this->rememberDoctorName($request, $doctorName);

        return redirect()->route('demo.preparing');
    }

    /** End the demo on purpose: destroy every trace, then return to login. */
    public function end(Request $request): RedirectResponse
    {
        if ($off = $this->rejectIfDisabled()) {
            return $off;
        }

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
        $session = config('demo.enabled') ? $this->activeSession($request) : null;

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

    /**
     * Where the visitor's own name is kept.
     *
     * In the demo session, deliberately — NOT on demo_sessions, which is a
     * marketing record on the production database. The name is typed to dress
     * one sandbox and is thrown away with it; it has no business outliving the
     * tenant it decorates.
     */
    protected const NAME_KEY = 'demo_doctor_name';

    /** Keep the name the visitor typed, if they typed one. */
    protected function rememberDoctorName(Request $request, ?string $name = null): void
    {
        $name = trim((string) ($name ?? $request->input('doctor_name')));

        if ($name === '') {
            $request->session()->forget(self::NAME_KEY);

            return;
        }

        // Cut here only to bound what goes into the session; DemoSeeder is
        // what decides whether it is usable as a name at all.
        $request->session()->put(self::NAME_KEY, Str::limit($name, 60, ''));
    }

    protected function rememberedDoctorName(): ?string
    {
        $name = session(self::NAME_KEY);

        return is_string($name) && $name !== '' ? $name : null;
    }

    /** The role this session was opened as. */
    protected function roleOf(DemoSession $session): string
    {
        return $session->started_role === 'assistant' ? 'assistant' : 'doctor';
    }

    /** The signed cookie that marks every later request as this demo. */
    protected function demoCookie(DemoSession $session): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            name: config('demo.cookie'),
            value: DemoContext::cookieValue($session->id),
            minutes: (int) config('demo.max_duration_minutes'),
            httpOnly: true,
            sameSite: 'lax',
        );
    }

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

        return redirect()->route($route)->withCookie($this->demoCookie($session));
    }

    protected function firstClinicId(DemoSession $session): ?int
    {
        return Doctor::find($session->doctor_id)?->clinics()->value('id');
    }

    /**
     * Nothing here may run with the demo switched off.
     *
     * StartDemoSession only redirects the connection when demo.enabled is
     * true, so a request arriving here after the master switch has been thrown
     * — an old cookie, a bookmarked URL — is pointed at PRODUCTION while still
     * carrying a demo session id. Logging in, purging or reseeding against
     * that would act on whichever production rows happen to share those ids.
     */
    protected function rejectIfDisabled(): ?RedirectResponse
    {
        if (config('demo.enabled')) {
            return null;
        }

        return redirect()->route('login')->withErrors([
            'email' => __('التجربة متوقفة مؤقتاً. برجاء المحاولة لاحقاً.'),
        ]);
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

    /** A live session whose clinic has not been built yet. */
    protected function unbuiltSession(Request $request): ?DemoSession
    {
        $session = $this->sessionFromCookie($request);

        return $session && $session->isActive() && ! $session->doctor_id ? $session : null;
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
