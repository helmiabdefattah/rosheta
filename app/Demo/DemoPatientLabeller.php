<?php

namespace App\Demo;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Doctor;
use Illuminate\Support\Collection;

/**
 * Names each demo patient after what their file demonstrates, rather than
 * giving them an invented identity.
 *
 * A visitor exploring the demo should be able to read the queue and know what
 * each row is there to show — "حالة تحت الكشف الآن", "حالة سابقة — روشتة
 * وتحاليل", "حالة بموعد قادم" — instead of meeting a cast of fictional people
 * and having to click each one to find out which feature it exercises.
 *
 * The label is DERIVED from the seeded rows, not hard-coded, so it stays true
 * however the appointments were cycled and whichever specialty was chosen. It
 * runs last, once the queue has been re-timed and any specialty overlay has
 * been applied.
 */
class DemoPatientLabeller
{
    public function label(Doctor $doctor): void
    {
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->with(['items', 'collections'])
            ->get()
            ->groupBy('client_id');

        $facts = $this->factsPerClient($doctor, $appointments);

        // Number within each role so six past cases are not six identical rows.
        $seen = [];

        foreach ($facts as $clientId => $fact) {
            $role = $fact['role'];
            $seen[$role] = ($seen[$role] ?? 0) + 1;

            Client::whereKey($clientId)->update([
                'name' => $this->compose($role, $seen[$role], $fact),
            ]);
        }
    }

    /**
     * What each patient's file actually contains, in the order the demo cares
     * about: the live case first, then today's queue, then the archive.
     *
     * @param  Collection<int, Collection<int, Appointment>>  $appointments
     * @return array<int, array{role: string, qualifiers: array<int, string>}>
     */
    protected function factsPerClient(Doctor $doctor, Collection $appointments): array
    {
        $facts = [];

        foreach ($appointments as $clientId => $visits) {
            if ($clientId === null) {
                continue;
            }

            $role = $this->roleFor($visits);
            $qualifiers = $this->qualifiersFor($doctor, (int) $clientId, $visits);

            // "حالة بموعد قادم — موعد قادم" says the same thing twice.
            if ($role === self::ROLE_UPCOMING) {
                $qualifiers = array_values(array_diff($qualifiers, ['موعد قادم']));
            }

            $facts[$clientId] = ['role' => $role, 'qualifiers' => $qualifiers];
        }

        // Sort so the live case is labelled "1" and the archive comes last.
        uasort($facts, fn ($a, $b) => array_search($a['role'], self::ROLE_ORDER, true)
            <=> array_search($b['role'], self::ROLE_ORDER, true));

        return $facts;
    }

    private const ROLE_UNDER_EXAM = 'under_exam';

    private const ROLE_WAITING = 'waiting';

    private const ROLE_DONE_TODAY = 'done_today';

    private const ROLE_UPCOMING = 'upcoming';

    private const ROLE_NO_SHOW = 'no_show';

    private const ROLE_PAST = 'past';

    private const ROLE_ORDER = [
        self::ROLE_UNDER_EXAM,
        self::ROLE_WAITING,
        self::ROLE_DONE_TODAY,
        self::ROLE_UPCOMING,
        self::ROLE_NO_SHOW,
        self::ROLE_PAST,
    ];

    private const ROLE_LABELS = [
        self::ROLE_UNDER_EXAM => 'حالة تحت الكشف الآن',
        self::ROLE_WAITING => 'حالة في الانتظار اليوم',
        self::ROLE_DONE_TODAY => 'حالة انتهى كشفها اليوم',
        self::ROLE_UPCOMING => 'حالة بموعد قادم',
        self::ROLE_NO_SHOW => 'حالة لم تحضر',
        self::ROLE_PAST => 'حالة سابقة',
    ];

    /** @param  Collection<int, Appointment>  $visits */
    protected function roleFor(Collection $visits): string
    {
        $today = now()->startOfDay();
        $endOfToday = now()->endOfDay();

        if ($visits->contains(fn (Appointment $a) => $a->status === 'under_examination')) {
            return self::ROLE_UNDER_EXAM;
        }

        $waitingToday = $visits->first(fn (Appointment $a) => in_array($a->status, ['scheduled', 'pending', 'confirmed'], true)
            && $a->scheduled_at !== null
            && $a->scheduled_at->between($today, $endOfToday));

        if ($waitingToday !== null) {
            return self::ROLE_WAITING;
        }

        $doneToday = $visits->first(fn (Appointment $a) => $a->status === 'completed'
            && $a->scheduled_at !== null
            && $a->scheduled_at->between($today, $endOfToday));

        if ($doneToday !== null) {
            return self::ROLE_DONE_TODAY;
        }

        if ($visits->contains(fn (Appointment $a) => in_array($a->status, ['missed', 'escaped'], true))) {
            return self::ROLE_NO_SHOW;
        }

        // A patient with a finished visit is an "examined case" first and
        // foremost — that file holds the diagnosis, prescription and results
        // the doctor came to look at. That they also have a booking next week
        // is a detail, and it is added as a qualifier rather than overriding
        // the whole label. Only a patient with nothing behind them is
        // introduced by their upcoming appointment.
        if ($visits->contains(fn (Appointment $a) => $a->status === 'completed')) {
            return self::ROLE_PAST;
        }

        return self::ROLE_UPCOMING;
    }

    /**
     * The features this patient's file lets the doctor try — the part of the
     * name that answers "what will I find if I open this one?".
     *
     * @param  Collection<int, Appointment>  $visits
     * @return array<int, string>
     */
    protected function qualifiersFor(Doctor $doctor, int $clientId, Collection $visits): array
    {
        $connection = $doctor->getConnectionName();
        $db = \Illuminate\Support\Facades\DB::connection($connection);

        $qualifiers = [];

        if ($db->table('patient_tests')->where('client_id', $clientId)->where('doctor_id', $doctor->id)->exists()) {
            $qualifiers[] = 'تحاليل';
        }

        if ($db->table('prescriptions')->where('client_id', $clientId)->where('doctor_id', $doctor->id)->exists()) {
            $qualifiers[] = 'روشتة';
        }

        // Only a visit that has actually happened can owe money. Counting
        // future bookings here marked every single patient as "مستحقات",
        // which made the word meaningless and the front desk look broken.
        $outstanding = $visits
            ->filter(fn (Appointment $a) => in_array($a->status, ['completed', 'under_examination'], true))
            ->sum(fn (Appointment $a) => $a->remainingAmount());

        if (round($outstanding, 2) > 0) {
            $qualifiers[] = 'مستحقات';
        }

        $client = $db->table('clients')->where('id', $clientId)->first();

        if ($client && $client->insurance_company_id) {
            $qualifiers[] = 'تأمين';
        }

        if ($client && filled($client->allergies) && $client->allergies !== '[]') {
            $qualifiers[] = 'حساسية دواء';
        }

        $today = now()->endOfDay();

        if ($visits->contains(fn (Appointment $a) => $a->scheduled_at?->gt($today)
            && $a->status !== 'cancelled')) {
            $qualifiers[] = 'موعد قادم';
        }

        // The name sits in a queue row; three facts is as much as it can carry
        // before it stops being readable at a glance.
        return array_slice($qualifiers, 0, 3);
    }

    /** e.g. "حالة سابقة 2 — روشتة وتحاليل ومستحقات". */
    protected function compose(string $role, int $index, array $fact): string
    {
        $name = self::ROLE_LABELS[$role].' '.$index;

        if ($fact['qualifiers'] === []) {
            return $name;
        }

        // Arabic joins the last item with "و" rather than a comma.
        $list = collect($fact['qualifiers']);
        $joined = $list->count() === 1
            ? $list->first()
            : $list->slice(0, -1)->implode('، ').' و'.$list->last();

        return $name.' — '.$joined;
    }
}
