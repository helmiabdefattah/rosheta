<?php

namespace App\Demo;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\MedicalRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Makes each booking in the days ahead a consequence of a visit that already
 * happened, instead of an unexplained row on the calendar.
 *
 * A demo whose future days are full of "كشف أول" for patients who each have a
 * thick file behind them reads as random data. A real clinic's next few days
 * are mostly the follow-ups its last few days produced: the patient the doctor
 * saw on Monday comes back on Thursday, and comes back for a REASON — to
 * review a diagnosis, or to bring the result of something ordered at the visit
 * and not yet filed.
 *
 * So every upcoming appointment whose patient has a file is retyped as a
 * follow-up and given its reason from that file. What is left as a plain first
 * visit is exactly what should be: the patients with nothing behind them.
 *
 * Runs AFTER the specialty overlay, which rewrites every appointment reason
 * with the specialty's generic list — these reasons are specific to one
 * patient's history and have to survive it.
 */
class DemoFollowUps
{
    /** How much of a diagnosis fits in a queue row's reason column. */
    private const REASON_LIMIT = 45;

    public function link(Doctor $doctor, Clinic $clinic, Carbon $t0): void
    {
        $upcoming = Appointment::where('doctor_id', $doctor->id)
            ->where('scheduled_at', '>', $t0->copy()->endOfDay())
            ->whereNotNull('client_id')
            ->orderBy('scheduled_at')
            ->get();

        foreach ($upcoming as $appointment) {
            $visit = $this->lastVisit($doctor, (int) $appointment->client_id, $t0);

            // No file behind them: a genuinely new patient, booked and nothing
            // more. Left exactly as it is — the demo needs that case too.
            if ($visit === null) {
                continue;
            }

            $this->markAsFollowUp($appointment, $clinic, $visit);
        }
    }

    /** The patient's most recent finished visit with this doctor. */
    protected function lastVisit(Doctor $doctor, int $clientId, Carbon $t0): ?Appointment
    {
        return Appointment::where('doctor_id', $doctor->id)
            ->where('client_id', $clientId)
            ->where('status', 'completed')
            ->where('scheduled_at', '<', $t0->copy()->startOfDay())
            ->orderByDesc('scheduled_at')
            ->first();
    }

    /**
     * Retype the booking as a follow-up of $visit and say what it is for.
     *
     * The reason comes from whatever that visit actually left behind, in the
     * order a doctor would care about it: an order still waiting for its result
     * first, then the diagnosis being followed, then the bare date.
     */
    protected function markAsFollowUp(Appointment $appointment, Clinic $clinic, Appointment $visit): void
    {
        $on = $visit->scheduled_at?->toDateString() ?? '';
        $pending = $this->pendingOrder($visit);
        $diagnosis = Diagnosis::where('appointment_id', $visit->id)->value('diagnosis');

        if ($pending !== null) {
            $reason = 'متابعة نتيجة '.$pending;
        } elseif (filled($diagnosis)) {
            $reason = 'متابعة — '.Str::limit((string) $diagnosis, self::REASON_LIMIT);
        } else {
            $reason = 'متابعة بعد زيارة '.$on;
        }

        $notes = 'متابعة لزيارة '.$on
            .(filled($diagnosis) ? ' — التشخيص: '.Str::limit((string) $diagnosis, self::REASON_LIMIT) : '')
            .($pending !== null ? ' — مطلوب إحضار نتيجة '.$pending : '');

        $appointment->forceFill([
            'type' => 'follow_up',
            'price' => $clinic->follow_up_price,
            'reason' => $reason,
            'notes' => $notes,
        ])->save();
    }

    /**
     * Something ordered at that visit whose result has not been filed yet —
     * the most concrete reason a patient is coming back.
     */
    protected function pendingOrder(Appointment $visit): ?string
    {
        return MedicalRequest::where('appointment_id', $visit->id)
            ->where('status', '!=', 'completed')
            ->value('name');
    }
}
