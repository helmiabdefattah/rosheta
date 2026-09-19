<?php

namespace App\Demo;

use App\Models\DemoActivityEvent;
use App\Models\DemoSession;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Turns a finished demo run into something a human can read, at the last
 * moment it is still possible.
 *
 * Two sources, and both of them expire:
 *
 *  - the event trail (DemoActivityRecorder), which survives the tenant, and
 *  - the tenant itself, which is hard-deleted seconds later — so what the
 *    visitor CREATED has to be counted before DemoPurger runs, or the answer
 *    is gone for good.
 *
 * Counting rows alone would credit the visitor with the seeded clinic, so a
 * baseline is taken the moment seeding finishes and everything is reported as
 * a difference against it.
 *
 * The whole class is defensive: a failed analysis must never stop a purge.
 * Leaving a tenant alive because a COUNT threw would be a data-protection
 * problem, and the analysis is only marketing.
 */
class DemoActivityAnalyzer
{
    /**
     * What to count in the tenant, and how each table hangs off the doctor.
     * 'doctor'      — a doctor_id column.
     * 'appointment' — belongs to one of the doctor's appointments.
     * 'prescription'— belongs to one of the doctor's prescriptions.
     */
    private const TENANT_TABLES = [
        'appointments' => ['doctor', 'الحجوزات', 'Appointments'],
        'clients' => ['patients', 'المرضى', 'Patients'],
        'diagnoses' => ['doctor', 'التشخيصات', 'Diagnoses'],
        'prescriptions' => ['doctor', 'الروشتات', 'Prescriptions'],
        'prescription_items' => ['prescription', 'أدوية في الروشتات', 'Prescribed medicines'],
        'medical_requests' => ['doctor', 'طلبات التحاليل والأشعة', 'Test & imaging orders'],
        'patient_tests' => ['doctor', 'نتائج التحاليل', 'Test results'],
        'clinical_chart_notes' => ['doctor', 'ملاحظات على الملف', 'Chart notes'],
        'examination_field_values' => ['appointment', 'حقول كشف مملوءة', 'Examination values filled'],
        'collections' => ['appointment', 'عمليات التحصيل', 'Payments collected'],
        'appointment_items' => ['appointment', 'بنود الحساب', 'Billed items'],
        'appointment_insurances' => ['appointment', 'زيارات بتأمين', 'Insured visits'],
        'attachments' => ['appointment', 'المرفقات', 'Attachments'],
        'examination_fields' => ['doctor', 'حقول كشف مُعدّة', 'Examination fields configured'],
        'medical_plans' => ['doctor', 'الخطط العلاجية', 'Medical plans'],
        'billable_items' => ['doctor', 'بنود التسعيرة', 'Price-list items'],
    ];

    /**
     * The ladder a clinic actually climbs. Each rung is the action slugs that
     * prove it was reached — the answer to "how far did they get before they
     * stopped", which is the one number this whole feature exists to produce.
     */
    private const LADDER = [
        'opened' => [['demo.built', 'practice.doctor.dashboard', 'practice.assistant.dashboard'], 'دخل النظام', 'Opened the workspace'],
        'browsed' => [['practice.patients.show', 'practice.doctor.examine', 'practice.doctor.manager.patients'], 'فتح ملف مريض', 'Opened a patient'],
        'booked' => [['practice.assistant.appointments.store', 'practice.kiosk.book', 'practice.kiosk.store', 'practice.assistant.pending.confirm'], 'حجز موعداً', 'Booked a visit'],
        'examined' => [['practice.doctor.diagnosis.store', 'practice.doctor.examination-values.store', 'practice.doctor.diagnoses.update'], 'كشف وشخّص', 'Examined & diagnosed'],
        'prescribed' => [['practice.doctor.prescriptions.store'], 'كتب روشتة', 'Wrote a prescription'],
        'ordered' => [['practice.doctor.requests.store', 'practice.doctor.tests.store'], 'طلب تحاليل', 'Ordered tests'],
        'billed' => [['practice.collections.store', 'practice.appointment-items.store', 'practice.appointments.price'], 'حصّل مقابل الزيارة', 'Took payment'],
        'printed' => [['practice.prescriptions.print-thermal', 'practice.prescriptions.pdf', 'practice.assistant.appointments.print-ticket', 'practice.kiosk.prescription.print', 'demo.print.prescription', 'demo.print.ticket'], 'طبع للمريض', 'Printed for the patient'],
        'configured' => [['practice.doctor.clinic.update', 'practice.doctor.setup.examination-fields.store', 'practice.doctor.setup.medical-plans.store'], 'عدّل إعدادات العيادة', 'Configured the clinic'],
    ];

    public function connection(): ConnectionInterface
    {
        return DB::connection(config('demo.connection'));
    }

    /**
     * Remember what the seeded clinic contained, so everything counted later
     * is the visitor's own work and not the fixture's.
     */
    public function baseline(DemoSession $session): void
    {
        if ($session->doctor_id === null) {
            return;
        }

        try {
            $session->forceFill([
                'baseline_counts' => $this->tenantCounts((int) $session->doctor_id),
            ])->save();
        } catch (\Throwable $e) {
            Log::warning('Demo baseline not taken', [
                'demo_session' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Write the analysis of a run. Called immediately BEFORE the tenant is
     * deleted — the counts cannot be taken afterwards, and the answer has to
     * exist whether or not the visitor filled in the exit survey.
     *
     * Idempotent enough to be safe: a second call simply recomputes from the
     * event trail, and skips tenant counts once the tenant is gone.
     */
    public function finalize(DemoSession $session, ?string $reason = null): void
    {
        try {
            $summary = $this->buildSummary($session, $reason);

            $session->forceFill([
                'activity_summary' => $summary,
                'actions_count' => $summary['events']['actions'],
                'pages_count' => $summary['events']['pages'],
                'active_seconds' => $summary['active_seconds'],
                'analyzed_at' => now(),
            ])->save();
        } catch (\Throwable $e) {
            // Never block a purge. A run with no analysis is a loss; a tenant
            // that outlives its window is a problem.
            Log::error('Demo analysis failed', [
                'demo_session' => $session->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile().':'.$e->getLine(),
            ]);
        }
    }

    /**
     * The analysis itself, as stored in demo_sessions.activity_summary.
     */
    public function buildSummary(DemoSession $session, ?string $reason = null, bool $countTenant = true): array
    {
        $events = DemoActivityEvent::where('demo_session_id', $session->id)
            ->orderBy('occurred_at')
            ->get();

        // The admin list asks for a preview of runs that have not ended yet,
        // and counting sixteen tables per row would make the page crawl. The
        // trail alone answers everything except "what did they create", which
        // for a live run is not final anyway.
        $created = $this->withCarried(
            $session,
            $countTenant ? $this->createdByVisitor($session) : null
        );

        $actions = $events->where('kind', 'action');
        $pages = $events->where('kind', 'page');

        $first = $events->first()?->occurred_at ?? $session->started_at;
        $last = $events->last()?->occurred_at ?? $session->ended_at;

        return [
            'version' => 1,
            'reason' => $reason ?? $session->end_reason,

            'events' => [
                'total' => $events->count(),
                'actions' => $actions->count(),
                'pages' => $pages->count(),
            ],

            'active_seconds' => $first && $last ? (int) max(0, round($first->diffInSeconds($last))) : null,

            // What they actually produced inside the clinic.
            'created' => $created,

            // Where their attention went, action counts first.
            'features' => $this->featureBreakdown($events),

            // The individual things they did, most-repeated first.
            'top_actions' => $this->topActions($actions),

            // How the time split between the two roles.
            'roles' => [
                'doctor' => $events->where('role', 'doctor')->count(),
                'assistant' => $events->where('role', 'assistant')->count(),
            ],

            // How far up the ladder they got.
            'ladder' => $this->ladder($events),

            'first_at' => $first?->toIso8601String(),
            'last_at' => $last?->toIso8601String(),

            // False when the tenant was already gone — the journey is still
            // complete, only the "created" counts are unavailable.
            'tenant_counted' => $countTenant && $created !== null,
        ];
    }

    /**
     * Bank what the visitor built before "أعد التجربة" throws the tenant away.
     *
     * A reset wipes the clinic and seeds a new one, so the rows they created
     * in the first pass would otherwise vanish from the analysis and make an
     * engaged visitor look like they did nothing. The counts are added to a
     * running total on the session and merged back in at the end.
     */
    public function carryOverBeforeReset(DemoSession $session): void
    {
        try {
            $created = $this->createdByVisitor($session);

            if (empty($created)) {
                return;
            }

            $summary = (array) ($session->activity_summary ?? []);
            $carried = (array) ($summary['carried_created'] ?? []);

            foreach ($created as $table => $row) {
                $carried[$table] = [
                    'count' => (int) ($carried[$table]['count'] ?? 0) + $row['count'],
                    'ar' => $row['ar'],
                    'en' => $row['en'],
                ];
            }

            $summary['carried_created'] = $carried;

            $session->forceFill([
                'activity_summary' => $summary,
                // The next tenant gets its own baseline from build().
                'baseline_counts' => null,
            ])->save();
        } catch (\Throwable $e) {
            Log::warning('Demo reset carry-over failed', [
                'demo_session' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // =====================================================================

    /** Fold anything banked by an earlier reset into this run's totals. */
    protected function withCarried(DemoSession $session, ?array $created): ?array
    {
        $carried = (array) (($session->activity_summary['carried_created'] ?? []));

        if ($carried === []) {
            return $created;
        }

        $created ??= [];

        foreach ($carried as $table => $row) {
            $created[$table] = [
                'count' => (int) ($created[$table]['count'] ?? 0) + (int) $row['count'],
                'ar' => $created[$table]['ar'] ?? $row['ar'],
                'en' => $created[$table]['en'] ?? $row['en'],
            ];
        }

        uasort($created, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $created;
    }

    /**
     * What exists in the tenant now, minus what the seeder put there.
     *
     * @return array<string, array{count:int, ar:string, en:string}>|null
     */
    protected function createdByVisitor(DemoSession $session): ?array
    {
        if ($session->doctor_id === null) {
            return null;
        }

        try {
            $final = $this->tenantCounts((int) $session->doctor_id);
        } catch (\Throwable $e) {
            Log::warning('Demo tenant could not be counted', [
                'demo_session' => $session->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        $baseline = (array) ($session->baseline_counts ?? []);
        $created = [];

        foreach ($final as $table => $count) {
            // A negative difference means they deleted seeded rows, which is
            // its own kind of trying-things-out but not something to report as
            // "created".
            $delta = $count - (int) ($baseline[$table] ?? 0);

            if ($delta > 0) {
                $created[$table] = [
                    'count' => $delta,
                    'ar' => self::TENANT_TABLES[$table][1] ?? $table,
                    'en' => self::TENANT_TABLES[$table][2] ?? $table,
                ];
            }
        }

        // Most substantial first — the admin reads the top two and stops.
        uasort($created, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $created;
    }

    /**
     * Row counts for one tenant.
     *
     * @return array<string,int>
     */
    public function tenantCounts(int $doctorId): array
    {
        $db = $this->connection();
        $counts = [];

        foreach (self::TENANT_TABLES as $table => [$scope]) {
            try {
                $counts[$table] = match ($scope) {
                    'doctor' => $db->table($table)->where('doctor_id', $doctorId)->count(),

                    'appointment' => $db->table($table)
                        ->whereIn('appointment_id', fn ($q) => $q->select('id')->from('appointments')->where('doctor_id', $doctorId))
                        ->count(),

                    'prescription' => $db->table($table)
                        ->whereIn('prescription_id', fn ($q) => $q->select('id')->from('prescriptions')->where('doctor_id', $doctorId))
                        ->count(),

                    // Patients are not tenant-keyed; they are whoever this
                    // doctor has an appointment with.
                    'patients' => $db->table('appointments')
                        ->where('doctor_id', $doctorId)
                        ->whereNotNull('client_id')
                        ->distinct()
                        ->count('client_id'),

                    default => 0,
                };
            } catch (\Throwable) {
                // A table this installation does not have is not a failure of
                // the analysis — every other number still stands.
                continue;
            }
        }

        return $counts;
    }

    /** Event counts per feature, busiest first. */
    protected function featureBreakdown($events): array
    {
        $features = [];

        foreach ($events as $event) {
            $key = $event->meta['feature'] ?? 'demo';

            $features[$key] ??= ['actions' => 0, 'pages' => 0, 'total' => 0];
            $features[$key]['total']++;

            if ($event->kind === 'action') {
                $features[$key]['actions']++;
            } elseif ($event->kind === 'page') {
                $features[$key]['pages']++;
            }
        }

        uasort($features, fn ($a, $b) => [$b['actions'], $b['total']] <=> [$a['actions'], $a['total']]);

        return $features;
    }

    /** The things they did, collapsed and counted. */
    protected function topActions($actions): array
    {
        $grouped = [];

        foreach ($actions as $event) {
            $grouped[$event->action] ??= [
                'action' => $event->action,
                'ar' => $event->label,
                'en' => $event->label_en,
                'count' => 0,
            ];

            $grouped[$event->action]['count']++;
        }

        usort($grouped, fn ($a, $b) => $b['count'] <=> $a['count']);

        return array_slice(array_values($grouped), 0, 15);
    }

    /**
     * Which rungs of the ladder the run reached.
     *
     * @return array<string, array{reached:bool, ar:string, en:string}>
     */
    protected function ladder($events): array
    {
        $seen = $events->pluck('action')->unique()->all();
        $ladder = [];

        foreach (self::LADDER as $key => [$slugs, $ar, $en]) {
            $ladder[$key] = [
                'reached' => array_intersect($slugs, $seen) !== [],
                'ar' => $ar,
                'en' => $en,
            ];
        }

        return $ladder;
    }
}
