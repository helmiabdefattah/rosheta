<?php

namespace App\Demo;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Services\ClinicOnboardingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates one demo tenant: a doctor, their clinic, an assistant, and a clinic
 * that looks like it has been running for months and is mid-session right now.
 *
 * It COMPOSES over ClinicOnboardingService rather than duplicating or editing
 * it — that service is production onboarding code used by the admin panel, and
 * it already builds patients, past visits, diagnoses, prescriptions, lab
 * requests and results, billable items, plans, collections and insurance
 * splits inside a single transaction.
 *
 * What this class adds on top is the thing the demo needs and onboarding does
 * not: everything is positioned relative to T0 — the moment the visitor pressed
 * the button — so there is always a patient in the room "now" and a queue
 * waiting in the next two hours, whatever time of day they arrive.
 */
class DemoSeeder
{
    /** Minutes before T0 that the in-progress visit started. */
    private const IN_PROGRESS_STARTED_MINUTES_AGO = 12;

    /** Minutes after T0 for the patients still waiting today. */
    private const WAITING_OFFSETS = [20, 45, 75, 120];

    /** Minutes before T0 for the visits already finished today. */
    private const FINISHED_OFFSETS = [-125, -65];

    public function __construct(private readonly ClinicOnboardingService $onboarding)
    {
    }

    /**
     * @return array{doctor: Doctor, clinic: Clinic, doctor_user: \App\Models\User, assistant_user: \App\Models\User, stats: array}
     */
    public function seed(string $demoSessionId, ?string $specialty = null): array
    {
        $t0 = $this->t0();
        $profile = SpecialtyProfile::forSlug($specialty);

        $result = $this->onboarding->onboard($this->input($specialty, $profile));

        /** @var Doctor $doctor */
        $doctor = $result['doctor'];
        /** @var Clinic $clinic */
        $clinic = $result['clinic'];

        // onboard() is transactional, so a failure inside it leaves nothing
        // behind. Everything after it is not covered by that transaction, so a
        // failure here would strand a half-built tenant the caller cannot even
        // identify — it has no demo_session_id yet. Clean it up ourselves.
        try {
            DB::transaction(function () use ($doctor, $clinic, $demoSessionId, $t0, $profile) {
                $this->markAsDemoTenant($doctor, $demoSessionId, $profile);
                $this->openClinicAround($clinic, $t0);
                $this->retimeTodayAround($doctor, $clinic, $t0);

                if ($profile !== null) {
                    app(SpecialtyOverlay::class)->apply($doctor, $clinic, $profile);
                }

                $this->addBookingOnlyCases($doctor, $clinic, $t0);

                // Last: the label describes the finished state of each file, so
                // it has to run after the queue is re-timed and the specialty
                // overlay has been applied.
                app(DemoPatientLabeller::class)->label($doctor);
            });
        } catch (\Throwable $e) {
            app(DemoPurger::class)->purgeDoctor($doctor->id);

            throw $e;
        }

        return $result;
    }

    /**
     * "Now" in the demo timezone, rounded down to five minutes so the seeded
     * schedule reads as deliberate rather than arbitrary.
     */
    public function t0(): Carbon
    {
        $now = Carbon::now(config('demo.timezone'));

        return $now->copy()->setTime($now->hour, $now->minute - ($now->minute % 5), 0);
    }

    /** Input for the onboarding service: a full clinic, not a sparse one. */
    protected function input(?string $specialty, ?array $profile): array
    {
        $doctorName = $this->doctorName();

        return [
            'doctor_name' => $doctorName,
            'specialization_id' => $this->specializationId($specialty),
            // Without this the onboarding service falls back to its own
            // "{name} Clinic", which reads oddly in an Arabic workspace.
            'clinic_name' => str_replace(
                '{doctor}',
                $doctorName,
                $profile['clinic_name'] ?? 'عيادة {doctor}'
            ),
            'brief' => $profile['brief'] ?? 'حساب تجريبي — بيئة تجربة نظام مستشفى أون.',
            'address' => 'التجمع الخامس، القاهرة الجديدة',
            'phone_number' => '0100 000 0000',
            'medical_examination_price' => $profile['prices']['examination'] ?? 400,
            'follow_up_price' => $profile['prices']['follow_up'] ?? 150,
            'appointment_duration' => 30,

            // Opening hours are rewritten around T0 immediately afterwards;
            // these are only the starting point the service needs.
            'open_from' => '09:00',
            'open_to' => '21:00',
            'closed_days' => $this->closedDays(),

            // A clinic with history, a queue today, and a filling week ahead.
            'seed_demo' => true,
            'seed_history' => true,
            'patients_count' => 18,
            'history_visits' => 12,
            'appointments_per_day' => 6,
            'days_ahead' => 6,
        ];
    }

    /** Flag the tenant so the middleware, the purge and the demo bar can find it. */
    protected function markAsDemoTenant(Doctor $doctor, string $demoSessionId, ?array $profile): void
    {
        $doctor->forceFill([
            'is_demo' => true,
            'demo_session_id' => $demoSessionId,
            'demo_expires_at' => now()->addMinutes((int) config('demo.max_duration_minutes')),
            'demo_last_activity_at' => now(),
            'demo_template_key' => $profile['label'] ?? 'general_v1',
        ])->save();
    }

    /**
     * Put the working day around T0 (T0-3h to T0+5h), clamped into a plausible
     * range, so a doctor who opens the demo at 2am still lands inside opening
     * hours and sees a live queue instead of a closed clinic.
     */
    protected function openClinicAround(Clinic $clinic, Carbon $t0): void
    {
        $from = $t0->copy()->subHours(3);
        $to = $t0->copy()->addHours(5);

        // Keep it within one calendar day so the hours read normally in settings.
        if ($from->day !== $t0->day) {
            $from = $t0->copy()->startOfDay();
        }

        if ($to->day !== $t0->day) {
            $to = $t0->copy()->endOfDay()->setTime(23, 30);
        }

        $fromTime = $from->format('H:i');
        $toTime = $to->format('H:i');

        $closedDays = $this->closedDays();

        foreach (Clinic::DAYS as $day) {
            $closed = in_array($day, $closedDays, true);

            $attributes = [
                'from' => $closed ? null : $fromTime,
                'to' => $closed ? null : $toTime,
                'is_closed' => $closed,
            ];

            $clinic->workingHours()->updateOrCreate(['day' => $day], $attributes);
            $clinic->clinicDoctorWorkingHours()->updateOrCreate(
                ['day' => $day, 'doctor_id' => $clinic->doctor_id],
                $attributes
            );
        }

        $clinic->syncOpeningHoursFromWorkingHours();
    }

    /**
     * Move today's appointments onto the clock the visitor is actually looking
     * at: two finished earlier, one patient in the room since T0-12m, and the
     * rest waiting over the next two hours.
     *
     * The onboarding service already produces this SHAPE for today (completed,
     * completed, under_examination, scheduled…, pending) — all that is missing
     * is that it starts at the clinic's opening time rather than at "now".
     */
    protected function retimeTodayAround(Doctor $doctor, Clinic $clinic, Carbon $t0): void
    {
        $today = Appointment::where('doctor_id', $doctor->id)
            ->where('clinic_id', $clinic->id)
            ->whereBetween('scheduled_at', [$t0->copy()->startOfDay(), $t0->copy()->endOfDay()])
            ->orderBy('scheduled_at')
            ->get();

        $finished = $today->whereIn('status', ['completed'])->values();
        $inProgress = $today->firstWhere('status', 'under_examination');
        $waiting = $today->whereNotIn('status', ['completed', 'under_examination'])->values();

        foreach ($finished as $i => $appointment) {
            $offset = self::FINISHED_OFFSETS[$i] ?? (-150 - ($i * 30));
            $this->moveTo($appointment, $t0->copy()->addMinutes($offset), $i + 1);
        }

        if ($inProgress !== null) {
            $this->moveTo(
                $inProgress,
                $t0->copy()->subMinutes(self::IN_PROGRESS_STARTED_MINUTES_AGO),
                $finished->count() + 1
            );
        }

        foreach ($waiting as $i => $appointment) {
            $offset = self::WAITING_OFFSETS[$i] ?? (self::WAITING_OFFSETS[count(self::WAITING_OFFSETS) - 1] + (($i - count(self::WAITING_OFFSETS) + 1) * 30));

            $this->moveTo(
                $appointment,
                $t0->copy()->addMinutes($offset),
                $finished->count() + ($inProgress ? 1 : 0) + $i + 1
            );
        }
    }

    /**
     * Two cases that the onboarding service never produces, because every
     * patient it creates ends up with a history: a patient who is only booked
     * for next week, and one who booked and did not turn up.
     *
     * Both have deliberately empty files. That is the point — one shows what a
     * brand-new booking looks like before the first visit, the other feeds the
     * attendance report. Without them the demo has no "حالة بموعد قادم" and no
     * "حالة لم تحضر" to open.
     */
    protected function addBookingOnlyCases(Doctor $doctor, Clinic $clinic, Carbon $t0): void
    {
        $cases = [
            ['when' => $t0->copy()->addDays(2)->setTime(11, 30), 'status' => 'scheduled', 'source' => 'reservation'],
            ['when' => $t0->copy()->subDays(2)->setTime(12, 0), 'status' => 'missed', 'source' => 'system'],
        ];

        foreach ($cases as $case) {
            // Named by DemoPatientLabeller once the appointment exists.
            $client = Client::create([
                'name' => 'حالة جديدة',
                'phone_number' => $this->uniquePhone(),
                'email' => 'patient.'.Str::lower(Str::random(12)).'@demo.rosheta.test',
                'password' => Str::random(32),
                'gender' => 'female',
                'dob' => now()->subYears(31)->toDateString(),
            ]);

            Appointment::create([
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinic->id,
                'client_id' => $client->id,
                'scheduled_at' => $case['when'],
                'appointment_date' => $case['when']->toDateString(),
                'appointment_time' => $case['when']->format('H:i:s'),
                'queue_number' => 1,
                'source' => $case['source'],
                'type' => 'medical_examination',
                'price' => $clinic->medical_examination_price,
                'status' => $case['status'],
                'reason' => $case['status'] === 'missed' ? 'لم يحضر في الموعد' : 'كشف أول — حجز جديد',
            ]);
        }
    }

    /** Unique across users and clients, matching the onboarding convention. */
    protected function uniquePhone(): string
    {
        do {
            $phone = '01'.random_int(0, 2).str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (
            Client::where('phone_number', $phone)->exists()
            || \App\Models\User::where('phone_number', $phone)->exists()
        );

        return $phone;
    }

    /** Appointment carries three views of its time; keep them consistent. */
    protected function moveTo(Appointment $appointment, Carbon $when, int $queueNumber): void
    {
        $appointment->forceFill([
            'scheduled_at' => $when,
            'appointment_date' => $when->toDateString(),
            'appointment_time' => $when->format('H:i:s'),
            'queue_number' => $queueNumber,
        ])->save();
    }

    /**
     * Friday is the Egyptian clinic's day off — except when the visitor opens
     * the demo ON a Friday.
     *
     * The whole premise is a clinic that is live right now: a patient in the
     * room, a queue waiting. A closed clinic today would produce an empty
     * dashboard and the demo would look broken, which is worse than losing a
     * little realism about the weekend. A doctor evaluating software on their
     * day off is exactly the visitor we most need to convince.
     */
    protected function closedDays(): array
    {
        $today = strtolower($this->t0()->englishDayOfWeek);

        return $today === 'friday' ? [] : ['friday'];
    }

    protected function specializationId(?string $specialty): int
    {
        $query = Specialization::query();

        if ($specialty) {
            $match = (clone $query)->where('slug', $specialty)->orWhere('name', $specialty)->first();

            if ($match) {
                return $match->id;
            }
        }

        $internal = (clone $query)->where('slug', 'internal-medicine')->first();

        return $internal?->id
            ?? $query->orderBy('id')->value('id')
            ?? throw new \RuntimeException(
                'The demo database has no specializations. Run: php artisan demo:setup'
            );
    }

    /** A believable Egyptian doctor name, varied so concurrent demos differ. */
    protected function doctorName(): string
    {
        $first = ['أحمد', 'محمد', 'سامي', 'كريم', 'هشام', 'ياسر', 'طارق', 'عمرو'];
        $last = ['سامي', 'عبد الله', 'الشناوي', 'فؤاد', 'زكي', 'الجندي', 'رشدي', 'حلمي'];

        return 'د. '.$first[array_rand($first)].' '.$last[array_rand($last)];
    }
}
