<?php

namespace App\Demo;

use Carbon\Carbon;

/**
 * The few days around "now" that the demo clinic lives in.
 *
 * Everything the visitor sees is placed inside this window: the archive in the
 * last few days, the queue today, the bookings in the next few. The onboarding
 * service spreads its history over months and its bookings over a week, which
 * is realistic for a clinic that has been running — but in a demo it means the
 * calendar the visitor opens is empty on both sides of today, and a file dated
 * three months ago has nothing around it to connect to.
 *
 * The width of the window is configurable (demo.history_days / demo.future_days).
 * All arithmetic here is calendar arithmetic on purpose: Egypt observes DST, so
 * "three days ago at 10:00" is not "now minus 72 hours".
 */
class DemoWindow
{
    /** Spacing between two seeded visits on the same past day. */
    public const SLOT_MINUTES = 40;

    /** How far each wrapped lap of slots is pushed past the previous one. */
    private const LAP_OFFSET_MINUTES = 15;

    /** Hours of the working day before and after T0 (see DemoSeeder). */
    private const OPEN_BEFORE_HOURS = 3;

    private const OPEN_AFTER_HOURS = 5;

    public static function historyDays(): int
    {
        return max(1, (int) config('demo.history_days', 3));
    }

    public static function futureDays(): int
    {
        return max(1, (int) config('demo.future_days', 3));
    }

    /**
     * The clinic's working day on T0's date: T0-3h to T0+5h, kept inside one
     * calendar day so the hours still read normally in the settings screen.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function openingWindow(Carbon $t0): array
    {
        $from = $t0->copy()->subHours(self::OPEN_BEFORE_HOURS);
        $to = $t0->copy()->addHours(self::OPEN_AFTER_HOURS);

        if (! $from->isSameDay($t0)) {
            $from = $t0->copy()->startOfDay();
        }

        if (! $to->isSameDay($t0)) {
            $to = $t0->copy()->setTime(23, 30);
        }

        return [$from, $to];
    }

    /**
     * The working days inside the history window, oldest first.
     *
     * @param  array<int, string>  $closedDays  lowercase English day names
     * @return array<int, Carbon>
     */
    public static function pastWorkingDays(Carbon $t0, array $closedDays = []): array
    {
        return self::workingDays($t0, $closedDays, self::historyDays(), -1);
    }

    /**
     * @param  array<int, string>  $closedDays
     * @return array<int, Carbon>
     */
    public static function futureWorkingDays(Carbon $t0, array $closedDays = []): array
    {
        return self::workingDays($t0, $closedDays, self::futureDays(), 1);
    }

    /**
     * When the $index-th past visit happens: days are filled round-robin so the
     * archive is spread evenly, and the time walks down the working day.
     *
     * @param  array<int, string>  $closedDays
     */
    public static function pastSlot(Carbon $t0, int $index, array $closedDays = []): Carbon
    {
        return self::spreadOver(self::pastWorkingDays($t0, $closedDays), $t0, $index);
    }

    /**
     * The same, forward: the $index-th booking in the days ahead.
     *
     * Bookings have to be spread the same way the archive is, and for the same
     * reason — the onboarding service lays them out from the clinic's nominal
     * opening time, which is no longer where the demo's opening hours are once
     * they have been rewritten around T0. A visitor who opens the demo at 8pm
     * would otherwise page forward into tomorrow and find every booking sitting
     * hours before the clinic opens.
     *
     * @param  array<int, string>  $closedDays
     */
    public static function futureSlot(Carbon $t0, int $index, array $closedDays = []): Carbon
    {
        return self::spreadOver(self::futureWorkingDays($t0, $closedDays), $t0, $index);
    }

    /**
     * Round-robin across $days: consecutive indexes land on consecutive days,
     * and each wrap moves one slot down the working day.
     *
     * @param  array<int, Carbon>  $days
     */
    private static function spreadOver(array $days, Carbon $t0, int $index): Carbon
    {
        $day = $days[$index % count($days)];

        return self::slotOn($day, $t0, intdiv($index, count($days)));
    }

    /**
     * A time inside $day's working hours, $slot slots after it opens.
     *
     * More appointments than slots is normal — a visitor who opens the demo at
     * 10pm gets a four-hour working day and six patients booked into it — so
     * the slots wrap. Each lap is nudged a few minutes later than the last so a
     * wrapped appointment lands BETWEEN the earlier ones rather than exactly on
     * top of one: two rows at the same minute on the same day read as a bug,
     * whoever is looking.
     */
    public static function slotOn(Carbon $day, Carbon $t0, int $slot): Carbon
    {
        [$from, $to] = self::openingWindow($t0);

        $open = $day->copy()->setTime($from->hour, $from->minute, 0);
        $usable = max(self::SLOT_MINUTES, $from->diffInMinutes($to) - self::SLOT_MINUTES);

        $walked = $slot * self::SLOT_MINUTES;
        $lap = intdiv($walked, $usable);

        // LAP_OFFSET does not divide SLOT_MINUTES, so no lap can ever line up
        // with an earlier one until the eighth — which needs 40+ appointments
        // in a single day.
        return $open->addMinutes(($walked % $usable) + ($lap * self::LAP_OFFSET_MINUTES));
    }

    /**
     * @param  array<int, string>  $closedDays
     * @return array<int, Carbon>
     */
    private static function workingDays(Carbon $t0, array $closedDays, int $count, int $direction): array
    {
        $days = [];

        // Stop after a fortnight rather than looping forever if every day of
        // the week has somehow been marked closed.
        for ($step = 1; $step <= 14 && count($days) < $count; $step++) {
            $day = $t0->copy()->addDays($direction * $step)->startOfDay();

            if (! in_array(strtolower($day->englishDayOfWeek), $closedDays, true)) {
                $days[] = $day;
            }
        }

        if ($days === []) {
            $days[] = $t0->copy()->addDays($direction)->startOfDay();
        }

        return $direction < 0 ? array_reverse($days) : $days;
    }
}
