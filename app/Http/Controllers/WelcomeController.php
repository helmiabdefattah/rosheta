<?php

namespace App\Http\Controllers;

use App\Demo\DemoContext;
use App\Models\DemoSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The public landing page — and, on the demo installation, the front door.
 *
 * The demo sandbox runs as its own deployment (DEMO_ENABLED=true) whose only
 * audience is a visitor who came to try the clinic. The marketing site,
 * app-store buttons and patient copy belong to production and are noise there:
 * that deployment has no real accounts to sign into and no app to download.
 * So with the demo switched on, "/" stops being a page and becomes a
 * signpost — into the workspace if a demo is already running, to the sign-in
 * page (which in demo mode is the start card) if not. Anyone signed in
 * WITHOUT a demo — the deployment's own administrator — is forwarded on to
 * their dashboard by the login page itself, which already does that.
 *
 * With the demo switched off this is exactly what it always was: the landing
 * page, rendered.
 */
class WelcomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if (! config('demo.enabled')) {
            return view('welcome');
        }

        $session = $this->runningDemo($request);

        if ($session === null) {
            return redirect()->route('login');
        }

        // Claimed but not seeded yet: the visitor pressed the button and then
        // navigated away from the loading page. Put them back on it rather
        // than into a clinic that does not exist yet.
        if (! $session->doctor_id) {
            return redirect()->route('demo.preparing');
        }

        return redirect()->route($this->dashboardRoute($session));
    }

    /** The demo behind the cookie, only if it is still usable. */
    protected function runningDemo(Request $request): ?DemoSession
    {
        $id = DemoContext::readCookie($request);

        if ($id === null) {
            return null;
        }

        $session = DemoSession::find($id);

        return $session && $session->isActive() ? $session : null;
    }

    /**
     * Which workspace to land in: whoever is signed in, falling back to the
     * role the demo was opened as.
     *
     * Signed in beats started_role because switching role inside the demo
     * swaps the logged-in user without rewriting the session row — an
     * assistant who got there through the role switch must not be sent to the
     * doctor's dashboard by a column that still says "doctor".
     *
     * When nobody is signed in — the demo cookie outlived the login session —
     * the route's own `auth` middleware sends them to the login page, which is
     * where a visitor with a stale demo needs to be anyway.
     */
    protected function dashboardRoute(DemoSession $session): string
    {
        // Cast both sides: the id columns come back from MySQL as strings
        // under emulated prepares, and an identity check against an int
        // Auth::id() would quietly always miss.
        $userId = (int) Auth::id();

        $isAssistant = $userId !== 0
            ? $userId === (int) $session->assistant_user_id
            : $session->started_role === 'assistant';

        return $isAssistant
            ? 'practice.assistant.dashboard'
            : 'practice.doctor.dashboard';
    }
}
