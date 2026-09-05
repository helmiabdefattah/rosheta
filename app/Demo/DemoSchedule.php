<?php

namespace App\Demo;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Puts every appointment in the tenant on the clock the visitor is looking at.
 *
 * The onboarding service builds a plausible clinic, but it builds it around
 * midnight-relative dates and the clinic's nominal opening time. The demo needs
 * the same rows around T0 — the moment the button was pressed — and it needs
 * the three parts of the window to read differently from each other:
 *
 *   past days     what already happened: visits that were seen, plus the ones
 *                 that were cancelled and the ones nobody turned up for
 *   today         a queue mid-flow: two seen, one on the chair, the rest waiting
 *   future days   nothing but appointments still to come — booked, confirmed,
 *                 and platform requests the front desk has not accepted yet
 *
 * Without the last two shapes the demo has no attendance report worth opening
 * (every past row is a clean "completed") and no confirmation queue at all.
 */
class DemoSchedule
{
    /** Minutes before T0 that the in-progress visit started. */
    private const IN_PROGRESS_STARTED_MINUTES_AGO = 12;

    /** Minutes after T0 for the patients still waiting today. */
    private const WAITING_OFFSETS = [20, 45, 75, 120];

    /** Minutes before T0 for the visits already finished today. */
    private const FINISHED_OFFSETS = [-125, -65];

    /**
     * The three states an appointment that has not happened yet can be in,
     * cycled across the days ahead: booked by the clinic, confirmed by the
     * patient, and a platform request still waiting for the front desk.
     */
    private const UPCOMING_STATUSES = ['scheduled', 'confirmed', 'pending'];

    /** One cancellation and one no-show on each past working day. */
    private const UNATTENDED = [
        ['status' => 'cancelled', 'reason' => 'ألغى الموعد قبل الحضور', 'source' => 'reservation'],
        ['status' => 'missed', 'reason' => 'حجز ولم يحضر', 'source' => 'system'],
    ];

    /**
     * Move everything the onboarding service created into the demo window.
     * Runs before the specialty overlay and before the patient files are
     * completed, because both of those work off where a visit sits in time.
     */
    public function shape(Doctor $doctor, Clinic $clinic, Carbon $t0): void
    {
        $closedDays = $this->closedDays($clinic);

        $this->today($doctor, $clinic, $t0);
        $this->history($doctor, $t0, $closedDays);
        $this->upcoming($doctor, $t0, $closedDays);
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
    protected function today(Doctor $doctor, Clinic $clinic, Carbon $t0): void
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
     * Pull the archive into the last few days.
     *
     * The onboarding service spreads its past visits nine days apart, which
     * puts the oldest one three months back. For a demo that is the wrong
     * shape: the visitor opens yesterday and the day before on the calendar,
     * the reports default to "this week", and both come back empty while the
     * files that would have filled them sit months away. Everything the demo
     * has to show is worth more inside the window the visitor actually looks
     * at.
     *
     * Only visits BEFORE today move; today's queue has already been placed
     * around T0.
     *
     * @param  array<int, string>  $closedDays
     */
    protected function history(Doctor $doctor, Carbon $t0, array $closedDays): void
    {
        $past = Appointment::where('doctor_id', $doctor->id)
            ->where('scheduled_at', '<', $t0->copy()->startOfDay())
            ->orderBy('scheduled_at')
            ->get();

        $perDay = [];

        foreach ($past as $i => $appointment) {
            $when = DemoWindow::pastSlot($t0, $i, $closedDays);
            $key = $when->toDateString();
            $perDay[$key] = ($perDay[$key] ?? 0) + 1;

            $this->moveTo($appointment, $when, $perDay[$key]);
        }
    }

    /**
     * The days ahead: bookings only, spread evenly over the working days in
     * the window and cycled through the three states an appointment that has
     * not happened yet can be in.
     *
     * Nothing here is completed or cancelled, on purpose. The days ahead are
     * where the visitor tries confirming, moving and cancelling appointments
     * themselves, and a "finished" visit dated next Tuesday would make the
     * whole calendar suspect.
     *
     * @param  array<int, string>  $closedDays
     */
    protected function upcoming(Doctor $doctor, Carbon $t0, array $closedDays): void
    {
        $upcoming = Appointment::where('doctor_id', $doctor->id)
            ->where('scheduled_at', '>', $t0->copy()->endOfDay())
            ->orderBy('scheduled_at')
            ->get();

        $perDay = [];

        foreach ($upcoming as $i => $appointment) {
            $when = DemoWindow::futureSlot($t0, $i, $closedDays);
            $key = $when->toDateString();
            $perDay[$key] = ($perDay[$key] ?? 0) + 1;

            // Cycled by position WITHIN the day, not by the global index:
            // the days are filled round-robin, so a global index would hand
            // every appointment on Tuesday the same status and make each day
            // look like a single block.
            $status = self::UPCOMING_STATUSES[($perDay[$key] - 1) % count(self::UPCOMING_STATUSES)];

            $appointment->forceFill([
                'status' => $status,
                // "pending" is a request that came in from the patient app and
                // is waiting for the front desk; the rest were booked by the
                // clinic itself.
                'source' => $status === 'pending' ? 'reservation' : 'system',
            ]);

            $this->moveTo($appointment, $when, $perDay[$key]);
        }
    }

    /**
     * The appointments that did not happen: one cancelled and one no-show on
     * each past working day.
     *
     * They are given to patients who already have a file, because that is what
     * makes them worth opening — a cancellation is a row in someone's history,
     * next to the visits that did happen. (The one deliberately EMPTY no-show
     * is created separately by DemoSeeder; it is there to show what a file
     * looks like before the first visit.)
     *
     * Neither is charged: a visit that never happened owes nothing, and pricing
     * them would put phantom debt on the front desk's outstanding list — the
     * one number in the demo that has to be trustworthy.
     *
     * Runs LATE, after the specialty overlay, so these reasons survive it —
     * the overlay rewrites every appointment reason with the specialty's list,
     * and "ألغى الموعد قبل الحضور" is the whole point of the row.
     */
    public function addUnattended(Doctor $doctor, Clinic $clinic, Carbon $t0): void
    {
        $patients = $this->patientsWithHistory($doctor, $t0);

        if ($patients->isEmpty()) {
            return;
        }

        $days = DemoWindow::pastWorkingDays($t0, $this->closedDays($clinic));
        $picked = 0;

        foreach ($days as $day) {
            foreach (self::UNATTENDED as $case) {
                [$when, $queueNumber] = $this->freeSlotOn($doctor, $day, $t0);

                Appointment::create([
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $clinic->id,
                    'client_id' => $patients[$picked % $patients->count()],
                    'scheduled_at' => $when,
                    'appointment_date' => $when->toDateString(),
                    'appointment_time' => $when->format('H:i:s'),
                    'queue_number' => $queueNumber,
                    'source' => $case['source'],
                    'type' => 'follow_up',
                    'price' => 0,
                    'status' => $case['status'],
                    'reason' => $case['reason'],
                ]);

                $picked++;
            }
        }
    }

    /**
     * A time on $day that nothing is booked at yet, and the queue number to go
     * with it: one slot past everything already there.
     *
     * Rows added after the window has been laid out (the cancellations, the
     * no-shows, the empty new-patient cases) cannot use the round-robin index
     * the rest were placed with — that index only describes its own pass — so
     * they ask the day itself what is free.
     *
     * @return array{0: Carbon, 1: int}
     */
    public function freeSlotOn(Doctor $doctor, Carbon $day, Carbon $t0): array
    {
        $taken = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('scheduled_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->count();

        return [DemoWindow::slotOn($day, $t0, $taken), $taken + 1];
    }

    /**
     * Patients with a visit behind them.
     *
     * @return Collection<int, int>
     */
    protected function patientsWithHistory(Doctor $doctor, Carbon $t0): Collection
    {
        return Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->where('scheduled_at', '<', $t0->copy()->startOfDay())
            ->whereNotNull('client_id')
            ->orderBy('client_id')
            ->pluck('client_id')
            ->unique()
            ->values();
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
     * The clinic's own closed days — read back from the working hours rather
     * than recomputed, so nothing is ever scheduled on a day the settings
     * screen shows as shut.
     *
     * @return array<int, string>
     */
    protected function closedDays(Clinic $clinic): array
    {
        return $clinic->workingHours()
            ->where('is_closed', true)
            ->pluck('day')
            ->map(fn ($day) => strtolower((string) $day))
            ->all();
    }
}
