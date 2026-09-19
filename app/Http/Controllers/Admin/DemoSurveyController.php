<?php

namespace App\Http\Controllers\Admin;

use App\Demo\DemoActivityAnalyzer;
use App\Http\Controllers\Controller;
use App\Models\DemoActivityEvent;
use App\Models\DemoSession;
use App\Models\DemoSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * What happened in the demo sandbox — every run, not only the ones that left
 * a comment.
 *
 * The exit survey is answered by a small minority, and the runs that say
 * nothing are exactly the ones worth understanding: somebody who opened the
 * doctor screen, could not find how to write a prescription and left never
 * fills in a form about it. So the page is built around the RUN: what they
 * did, how far they got, what they created, and — if they bothered — what
 * they said.
 *
 * Read-only apart from deleting. There is nobody to reply to: a demo visitor
 * leaves no account, no email and no phone behind.
 */
class DemoSurveyController extends Controller
{
    /** How much of one journey the page will render before it truncates. */
    private const TIMELINE_LIMIT = 250;

    public function __construct(private readonly DemoActivityAnalyzer $analyzer)
    {
    }

    public function index(Request $request)
    {
        $query = DemoSession::with('survey');

        // What they said.
        if ($request->filled('useful')) {
            match ($request->input('useful')) {
                'yes' => $query->whereHas('survey', fn ($q) => $q->where('was_useful', true)),
                'no' => $query->whereHas('survey', fn ($q) => $q->where('was_useful', false)),
                'none' => $query->whereDoesntHave('survey'),
                default => null,
            };
        }

        if ($request->input('has_text') === '1') {
            $query->whereHas('survey', function ($q) {
                $q->whereNotNull('wants_added')->orWhereNotNull('wants_removed');
            });
        }

        // What they did.
        if ($request->input('engagement') === 'engaged') {
            $query->where('actions_count', '>', 0);
        } elseif ($request->input('engagement') === 'idle') {
            $query->where('actions_count', '=', 0);
        }

        if ($request->filled('role')) {
            $query->where('started_role', $request->input('role'));
        }

        if ($request->filled('reason')) {
            $query->where('end_reason', $request->input('reason'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('specialty', 'like', "%{$search}%")
                    ->orWhere('utm_source', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('survey', function ($s) use ($search) {
                        $s->where('wants_added', 'like', "%{$search}%")
                            ->orWhere('wants_removed', 'like', "%{$search}%");
                    });
            });
        }

        $sessions = $query->latest('started_at')->paginate(15)->withQueryString();

        return view('admin.demo-surveys.index', [
            'sessions' => $sessions,
            'analyses' => $this->analyses($sessions->getCollection()),
            'timelines' => $this->timelines($sessions->getCollection()),
            'stats' => $this->stats(),
            'orphanSurveys' => $this->orphanSurveys(),
        ]);
    }

    /** Remove one run: its journey, its answers, and the funnel record. */
    public function destroySession(DemoSession $demoSession)
    {
        DemoActivityEvent::where('demo_session_id', $demoSession->id)->delete();
        DemoSurvey::where('demo_session_id', $demoSession->id)->delete();
        $demoSession->delete();

        return redirect()->route('admin.demo-surveys.index')
            ->with('success', app()->getLocale() === 'ar'
                ? 'تم حذف التجربة وكل سجلها'
                : 'Trial run and its whole record deleted');
    }

    /** Remove only the written feedback, keeping the run itself. */
    public function destroy(DemoSurvey $demoSurvey)
    {
        $demoSurvey->delete();

        return redirect()->route('admin.demo-surveys.index')
            ->with('success', app()->getLocale() === 'ar'
                ? 'تم حذف الرأي بنجاح'
                : 'Survey response deleted');
    }

    // =====================================================================

    /**
     * The analysis for each run on this page.
     *
     * A finished run has one stored on it, written at the moment its tenant
     * was destroyed. A run that is still going does not, so one is built from
     * the event trail alone — without counting the tenant's tables, which
     * would mean sixteen COUNTs per row on a list of fifteen.
     *
     * @return array<string, array>
     */
    protected function analyses($sessions): array
    {
        $analyses = [];

        foreach ($sessions as $session) {
            $stored = $session->analysis();

            $analyses[$session->id] = ! empty($stored['events'])
                ? $stored
                : $this->analyzer->buildSummary($session, null, false);
        }

        return $analyses;
    }

    /**
     * The journeys themselves, in one query for the whole page.
     *
     * @return array<string, \Illuminate\Support\Collection>
     */
    protected function timelines($sessions): array
    {
        $ids = $sessions->pluck('id')->all();

        if ($ids === []) {
            return [];
        }

        return DemoActivityEvent::whereIn('demo_session_id', $ids)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get()
            ->groupBy('demo_session_id')
            ->map(fn ($events) => $events->take(self::TIMELINE_LIMIT))
            ->all();
    }

    /**
     * The header numbers, across every run rather than the current filter:
     * the question they answer is "how is the demo doing", which a filtered
     * ratio would answer wrongly.
     */
    protected function stats(): array
    {
        $sessions = DemoSession::query();

        $totals = (clone $sessions)
            ->selectRaw('COUNT(*) as runs')
            ->selectRaw('SUM(CASE WHEN actions_count > 0 THEN 1 ELSE 0 END) as engaged')
            ->selectRaw('AVG(actions_count) as avg_actions')
            ->selectRaw('AVG(active_seconds) as avg_active')
            ->first();

        $surveys = DemoSurvey::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN was_useful = 1 THEN 1 ELSE 0 END) as useful')
            ->selectRaw('SUM(CASE WHEN wants_added IS NOT NULL OR wants_removed IS NOT NULL THEN 1 ELSE 0 END) as with_text')
            ->first();

        return [
            'runs' => (int) ($totals->runs ?? 0),
            'engaged' => (int) ($totals->engaged ?? 0),
            'avg_actions' => round((float) ($totals->avg_actions ?? 0), 1),
            'avg_active_minutes' => round(((float) ($totals->avg_active ?? 0)) / 60, 1),
            'surveys' => (int) ($surveys->total ?? 0),
            'useful' => (int) ($surveys->useful ?? 0),
            'not_useful' => (int) ($surveys->total ?? 0) - (int) ($surveys->useful ?? 0),
            'with_text' => (int) ($surveys->with_text ?? 0),

            // Which rungs of the ladder the whole population reached — the
            // single most useful thing on the page: it says where people stop.
            'ladder' => $this->ladderReach(),

            // The features the demo population touched at all, busiest first.
            'features' => $this->featureReach(),
        ];
    }

    /**
     * How many runs reached each rung, counted from the trail rather than the
     * stored summaries so runs still in progress are included.
     */
    protected function ladderReach(): array
    {
        $ladder = [
            'opened' => ['demo.built', 'practice.doctor.dashboard', 'practice.assistant.dashboard'],
            'browsed' => ['practice.patients.show', 'practice.doctor.examine', 'practice.doctor.manager.patients'],
            'booked' => ['practice.assistant.appointments.store', 'practice.kiosk.book', 'practice.kiosk.store', 'practice.assistant.pending.confirm'],
            'examined' => ['practice.doctor.diagnosis.store', 'practice.doctor.examination-values.store', 'practice.doctor.diagnoses.update'],
            'prescribed' => ['practice.doctor.prescriptions.store'],
            'ordered' => ['practice.doctor.requests.store', 'practice.doctor.tests.store'],
            'billed' => ['practice.collections.store', 'practice.appointment-items.store', 'practice.appointments.price'],
            'printed' => ['practice.prescriptions.print-thermal', 'practice.prescriptions.pdf', 'practice.assistant.appointments.print-ticket', 'practice.kiosk.prescription.print', 'demo.print.prescription', 'demo.print.ticket'],
            'configured' => ['practice.doctor.clinic.update', 'practice.doctor.setup.examination-fields.store', 'practice.doctor.setup.medical-plans.store'],
        ];

        $reach = [];

        foreach ($ladder as $key => $slugs) {
            $reach[$key] = (int) DemoActivityEvent::whereIn('action', $slugs)
                ->distinct()
                ->count('demo_session_id');
        }

        return $reach;
    }

    /** Runs that touched each feature at all. */
    protected function featureReach(): array
    {
        $rows = DemoActivityEvent::query()
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.feature')) as feature")
            ->selectRaw('COUNT(DISTINCT demo_session_id) as runs')
            ->selectRaw('COUNT(*) as events')
            ->whereNotNull('meta')
            ->groupBy(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.feature'))"))
            ->orderByDesc('runs')
            ->get();

        return $rows->filter(fn ($r) => $r->feature !== null && $r->feature !== 'demo')
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * Answers that arrived without a usable token — kept, but attached to no
     * run, so they have nowhere else to appear.
     */
    protected function orphanSurveys()
    {
        return DemoSurvey::whereNull('demo_session_id')->latest()->limit(20)->get();
    }
}
