<?php

namespace App\Http\Controllers;

use App\Demo\DemoContext;
use App\Models\DemoSession;
use App\Models\DemoSurvey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The exit survey: three questions on the "demo ended" page.
 *
 *   1. Was the system useful?      yes / no   (required)
 *   2. Anything you would add?     free text
 *   3. Anything you disliked?      free text
 *
 * Asked after the run rather than during it, so answering costs the visitor
 * nothing they were in the middle of — by the time they see it the tenant is
 * already gone.
 *
 * Lives under /demo on purpose, like everything else the sandbox serves, so
 * StartDemoSession keeps the request on the demo session store. The row it
 * writes is the exception: DemoSurvey is pinned to production, because the
 * database this request is pointed at is the one being deleted.
 */
class DemoSurveyController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (! config('demo.enabled')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'was_useful' => 'required|in:0,1',
            'wants_added' => 'nullable|string|max:2000',
            'wants_removed' => 'nullable|string|max:2000',
        ]);

        // The signed token from the ended page. Unsigned ids are not accepted:
        // the form is public, and the id alone would let anyone attach answers
        // to a run that is not theirs.
        $token = (string) $request->input('token', '');
        $sessionId = DemoContext::verifyToken($token);
        $session = $sessionId ? DemoSession::find($sessionId) : null;

        $answers = [
            'was_useful' => (bool) $validated['was_useful'],
            'wants_added' => $this->clean($validated['wants_added'] ?? null),
            'wants_removed' => $this->clean($validated['wants_removed'] ?? null),
            'role' => $session?->started_role,
            'specialty' => $session?->specialty,
            'ip_hash' => hash('sha256', (string) $request->ip()),
        ];

        if ($sessionId !== null) {
            // A second submit from the same run is the visitor correcting
            // themselves, not a second opinion.
            DemoSurvey::updateOrCreate(['demo_session_id' => $sessionId], $answers);
        } else {
            // No usable token — keep the answers anyway, unattributed. Matching
            // on a null key here would overwrite somebody else's row.
            DemoSurvey::create($answers + ['demo_session_id' => null]);
        }

        return redirect()->route('demo.ended', array_filter([
            'reason' => $request->input('reason', 'user_ended'),
            't' => $token !== '' ? $token : null,
            'thanks' => 1,
        ]));
    }

    /** Blank-ish answers are noise in the admin list; store nothing instead. */
    protected function clean(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
