<?php

namespace App\Http\Controllers\Clinic\Concerns;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Shared month-calendar builder for the doctor and assistant dashboards.
 * Working days come from the doctor's clinic opening hours; booked counts are
 * scoped to the doctor so clinics sharing the appointments table stay isolated.
 *
 * Pass $scopeToClinic to narrow the counts to $clinic as well — the doctor
 * dashboard does, because it can switch clinics. The assistant dashboard must
 * not: it has no switcher, so scoping would permanently hide the other
 * clinics' appointments from them.
 */
trait BuildsClinicCalendar
{
    protected function buildCalendar(
        Request $request,
        Doctor $doctor,
        ?Clinic $clinic,
        Carbon $selectedDate,
        bool $scopeToClinic = false
    ): array {
        $openingHours = $clinic?->opening_hours ?? [];
        $inClinic = fn ($q) => $scopeToClinic && $clinic
            ? $q->where('clinic_id', $clinic->id)
            : $q;

        // The visible month follows ?month=, else the selected day's month.
        $month = Carbon::hasFormat((string) $request->query('month'), 'Y-m')
            ? Carbon::createFromFormat('Y-m', $request->query('month'))->startOfMonth()
            : $selectedDate->copy()->startOfMonth();

        // Egyptian week starts on Saturday.
        $gridStart = $month->copy()->startOfWeek(Carbon::SATURDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::FRIDAY);

        $counts = Appointment::where('doctor_id', $doctor->id)
            ->where($inClinic)
            ->whereBetween('scheduled_at', [$gridStart->copy()->startOfDay(), $gridEnd->copy()->endOfDay()])
            ->whereIn('status', ['scheduled', 'under_examination', 'completed'])
            ->get()
            ->groupBy(fn ($a) => optional($a->scheduled_at)->toDateString())
            ->map->count();

        // Pending rosheta-platform requests still awaiting front-desk confirmation.
        $pendingCounts = Appointment::where('doctor_id', $doctor->id)
            ->where($inClinic)
            ->whereBetween('scheduled_at', [$gridStart->copy()->startOfDay(), $gridEnd->copy()->endOfDay()])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->groupBy(fn ($a) => optional($a->scheduled_at)->toDateString())
            ->map->count();

        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = [
                    'date' => $cursor->copy(),
                    'inMonth' => $cursor->month === $month->month,
                    'isToday' => $cursor->isToday(),
                    'isSelected' => $cursor->isSameDay($selectedDate),
                    'isOpen' => $this->isOpenOn($openingHours, $cursor),
                    'count' => (int) ($counts[$cursor->toDateString()] ?? 0),
                    'pending' => (int) ($pendingCounts[$cursor->toDateString()] ?? 0),
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return [
            'month' => $month,
            'weeks' => $weeks,
            'prev' => $month->copy()->subMonth()->format('Y-m'),
            'next' => $month->copy()->addMonth()->format('Y-m'),
            'dayHeaders' => collect(Clinic::DAYS)->map(
                fn ($d) => Carbon::parse($d)->translatedFormat('D')
            )->all(),
        ];
    }

    /**
     * A day is open only when its weekday has hours and isn't marked closed.
     * A missing weekday is treated as closed.
     */
    protected function isOpenOn(array $openingHours, Carbon $date): bool
    {
        $hours = $openingHours[strtolower($date->englishDayOfWeek)] ?? null;

        return $hours ? ! ($hours['closed'] ?? false) : false;
    }
}
