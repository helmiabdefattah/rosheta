<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'user_id',
        'name',
        'address',
        'phone_number',
        'governorate_id',
        'city_id',
        'area_id',
        'latitude',
        'longitude',
        'medical_examination_price',
        'follow_up_price',
        'slots_per_interval',
        // Clinic (design) system fields.
        'opening_hours',
        'appointment_duration',
        'display_show_next_button',
        'printer_connected_at',
        'printer_language',
    ];

    /** Languages the Bluetooth ticket printer can print in. */
    public const PRINTER_LANGUAGES = ['ar', 'en'];

    /**
     * Days of the week in display order (Egyptian week starts on Saturday,
     * Friday is the usual weekend / closing day). Used by the clinic system.
     */
    public const DAYS = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    /** Max number of reservations per 30-min slot (default 1). */
    public function getSlotsPerInterval(): int
    {
        $v = $this->slots_per_interval ?? 1;
        return max(1, (int) $v);
    }

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'medical_examination_price' => 'decimal:2',
        'follow_up_price' => 'decimal:2',
        'opening_hours' => 'array',
        'appointment_duration' => 'integer',
        'display_show_next_button' => 'boolean',
        'printer_connected_at' => 'datetime',
    ];

    /**
     * Whether a staff mobile app with a connected Bluetooth printer has sent a
     * heartbeat recently (within [$minutes]). Used to auto-print queue tickets.
     */
    public function hasConnectedPrinter(int $minutes = 10): bool
    {
        return $this->printer_connected_at !== null
            && $this->printer_connected_at->gt(now()->subMinutes($minutes));
    }

    /**
     * This clinic's default price for a visit type. Examination types bill at
     * medical_examination_price; consultation / follow-up at follow_up_price —
     * the same mapping the admin booking screen uses. Null when unpriced.
     */
    public function priceFor(?string $type): ?float
    {
        $price = in_array($type, ['examination', 'medical_examination'], true)
            ? $this->medical_examination_price
            : $this->follow_up_price;

        return $price === null ? null : (float) $price;
    }

    /**
     * Language the ticket printer prints in. Falls back to the app locale so
     * clinics that never chose one keep today's behaviour.
     */
    public function printerLanguage(): string
    {
        return in_array($this->printer_language, self::PRINTER_LANGUAGES, true)
            ? $this->printer_language
            : config('app.locale');
    }

    /**
     * Clinic-system opening hours for a weekday name (e.g. "monday"), with
     * sane defaults so a freshly created clinic still renders a complete form.
     */
    public function hoursFor(string $day): array
    {
        $hours = $this->opening_hours[strtolower($day)] ?? null;

        return [
            'open' => $hours['open'] ?? '09:00',
            'close' => $hours['close'] ?? '17:00',
            'closed' => $hours['closed'] ?? false,
        ];
    }

    /** Is the clinic open on a given calendar date (clinic-system hours)? */
    public function isOpenOnDay(Carbon $date): bool
    {
        return ! $this->hoursFor($date->englishDayOfWeek)['closed'];
    }

    /**
     * The clinic's weekly schedule lives in two places for two UIs: the rosheta
     * booking side stores it in the workingHours table, the clinic ("practice")
     * side mirrors it in the opening_hours JSON. These two keep them in step so
     * both editors show the same days. workingHours is treated as canonical.
     */
    public function syncOpeningHoursFromWorkingHours(): void
    {
        $rows = $this->workingHours()->get()->keyBy('day');

        $hours = [];
        foreach (self::DAYS as $day) {
            $wh = $rows->get($day);
            $closed = ! $wh || (bool) $wh->is_closed || ! $wh->from || ! $wh->to;

            $hours[$day] = [
                'open' => $closed ? null : Carbon::parse($wh->from)->format('H:i'),
                'close' => $closed ? null : Carbon::parse($wh->to)->format('H:i'),
                'closed' => $closed,
            ];
        }

        $this->update(['opening_hours' => $hours]);
    }

    /** Rebuild the workingHours rows from the opening_hours JSON (reverse sync). */
    public function syncWorkingHoursFromOpeningHours(): void
    {
        $hours = $this->opening_hours ?? [];

        $this->workingHours()->delete();
        foreach (self::DAYS as $day) {
            $h = $hours[$day] ?? null;
            $closed = $h ? (bool) ($h['closed'] ?? false) : true;

            $this->workingHours()->create([
                'day' => $day,
                'from' => $closed ? null : ($h['open'] ?? null),
                'to' => $closed ? null : ($h['close'] ?? null),
                'is_closed' => $closed,
            ]);
        }
    }

    /**
     * How many appointment slots fit on a given weekday, based on its open
     * window and the configured appointment duration. Returns 0 when closed.
     */
    public function slotsForDay(string $day): int
    {
        $hours = $this->hoursFor($day);

        if ($hours['closed'] || ! $hours['open'] || ! $hours['close']) {
            return 0;
        }

        $minutes = abs(Carbon::parse($hours['open'])->diffInMinutes(Carbon::parse($hours['close'])));
        $duration = max(1, (int) $this->appointment_duration);

        return (int) floor($minutes / $duration);
    }

    /** Roughly how many appointments fit in one hour at this clinic. */
    public function slotsPerHour(): float
    {
        $duration = max(1, (int) $this->appointment_duration);

        return round(60 / $duration, 1);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** All doctors linked to this clinic (many-to-many). Pivot may have medical_examination_price, follow_up_price. */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'clinic_doctor')
            ->withPivot('medical_examination_price', 'follow_up_price');
    }

    /** Per-doctor working hours (when clinic has multiple doctors). */
    public function clinicDoctorWorkingHours(): HasMany
    {
        return $this->hasMany(ClinicDoctorWorkingHour::class, 'clinic_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(ClinicWorkingHour::class, 'clinic_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get a short opening-hours summary (e.g. "Sat-Thu 9:00 AM - 5:00 PM" or "Today 9:00-17:00").
     */
    public function getOpeningHoursSummary(): ?string
    {
        $hours = $this->workingHours()->where('is_closed', false)->whereNotNull('from')->whereNotNull('to')->get();
        if ($hours->isEmpty()) {
            return null;
        }
        $today = strtolower(Carbon::now()->format('l'));
        $todayRow = $hours->firstWhere('day', $today);
        if ($todayRow) {
            $from = $todayRow->from instanceof \Carbon\Carbon ? $todayRow->from->format('g:i A') : \Carbon\Carbon::parse($todayRow->from)->format('g:i A');
            $to = $todayRow->to instanceof \Carbon\Carbon ? $todayRow->to->format('g:i A') : \Carbon\Carbon::parse($todayRow->to)->format('g:i A');
            return $from . ' - ' . $to;
        }
        $first = $hours->first();
        $from = $first->from instanceof \Carbon\Carbon ? $first->from->format('g:i A') : \Carbon\Carbon::parse($first->from)->format('g:i A');
        $to = $first->to instanceof \Carbon\Carbon ? $first->to->format('g:i A') : \Carbon\Carbon::parse($first->to)->format('g:i A');
        return $from . ' - ' . $to;
    }

    /**
     * Get the first available appointment slot in the next 14 days.
     * Returns ['date' => 'Y-m-d', 'time' => 'H:i', 'display' => 'formatted string'] or null.
     */
    public function getFirstAvailableSlot(): ?array
    {
        $start = Carbon::today();
        $end = $start->copy()->addDays(14);
        $booked = $this->appointments()
            ->where('appointment_date', '>=', $start)
            ->where('appointment_date', '<=', $end)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->groupBy(function ($a) {
                $d = $a->appointment_date instanceof \Carbon\Carbon ? $a->appointment_date->format('Y-m-d') : $a->appointment_date;
                $t = $a->appointment_time instanceof \Carbon\Carbon ? $a->appointment_time->format('H:i') : \Carbon::parse($a->appointment_time)->format('H:i');
                return $d . ' ' . $t;
            })
            ->map->count();

        $cap = $this->getSlotsPerInterval();
        $whByDay = $this->workingHours()->get()->keyBy('day');

        for ($i = 0; $i < 14; $i++) {
            $date = $start->copy()->addDays($i);
            $dayName = strtolower($date->format('l'));
            $wh = $whByDay->get($dayName);
            if (!$wh || $wh->is_closed || !$wh->from || !$wh->to) {
                continue;
            }
            $from = $wh->from instanceof \Carbon\Carbon ? $wh->from : \Carbon::parse($wh->from);
            $to = $wh->to instanceof \Carbon\Carbon ? $wh->to : \Carbon::parse($wh->to);
            $current = $from->copy();
            $dateStr = $date->format('Y-m-d');
            while ($current->format('H:i') < $to->format('H:i')) {
                $timeStr = $current->format('H:i');
                $key = $dateStr . ' ' . $timeStr;
                if (($booked->get($key) ?? 0) < $cap) {
                    $display = $date->format('M j, Y') . ' ' . $current->format('g:i A');
                    return ['date' => $dateStr, 'time' => $timeStr, 'display' => $display];
                }
                $current->addMinutes(30);
            }
        }
        return null;
    }

    /**
     * Get list of dates in the next $limit days that have at least one free slot.
     * Returns array of ['date' => 'Y-m-d', 'label' => 'formatted day label'].
     */
    public function getAvailableDays(int $limit = 14): array
    {
        $start = Carbon::today();
        $end = $start->copy()->addDays($limit);
        $booked = $this->appointments()
            ->where('appointment_date', '>=', $start)
            ->where('appointment_date', '<=', $end)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->groupBy(function ($a) {
                $d = $a->appointment_date instanceof \Carbon\Carbon ? $a->appointment_date->format('Y-m-d') : $a->appointment_date;
                $t = $a->appointment_time instanceof \Carbon\Carbon ? $a->appointment_time->format('H:i') : \Carbon::parse($a->appointment_time)->format('H:i');
                return $d . ' ' . $t;
            })
            ->map->count();

        $cap = $this->getSlotsPerInterval();
        $whByDay = $this->workingHours()->get()->keyBy('day');
        $available = [];

        for ($i = 0; $i < $limit; $i++) {
            $date = $start->copy()->addDays($i);
            $dayName = strtolower($date->format('l'));
            $wh = $whByDay->get($dayName);
            if (!$wh || $wh->is_closed || !$wh->from || !$wh->to) {
                continue;
            }
            $from = $wh->from instanceof \Carbon\Carbon ? $wh->from : \Carbon::parse($wh->from);
            $to = $wh->to instanceof \Carbon\Carbon ? $wh->to : \Carbon::parse($wh->to);
            $current = $from->copy();
            $dateStr = $date->format('Y-m-d');
            $hasSlot = false;
            while ($current->format('H:i') < $to->format('H:i')) {
                $timeStr = $current->format('H:i');
                if (($booked->get($dateStr . ' ' . $timeStr) ?? 0) < $cap) {
                    $hasSlot = true;
                    break;
                }
                $current->addMinutes(30);
            }
            if ($hasSlot) {
                $available[] = [
                    'date' => $dateStr,
                    'label' => $date->format('D, M j'),
                ];
            }
        }

        return $available;
    }

    /**
     * Get opening hours summary for a specific doctor at this clinic.
     * Uses clinic_doctor_working_hours when set, otherwise clinic working hours.
     */
    public function getOpeningHoursSummaryForDoctor(Doctor $doctor): ?string
    {
        $doctorHours = $this->clinicDoctorWorkingHours()
            ->where('doctor_id', $doctor->id)
            ->where('is_closed', false)
            ->whereNotNull('from')
            ->whereNotNull('to')
            ->get();
        $hours = $doctorHours->isNotEmpty() ? $doctorHours : $this->workingHours()->where('is_closed', false)->whereNotNull('from')->whereNotNull('to')->get();
        if ($hours->isEmpty()) {
            return null;
        }
        $today = strtolower(Carbon::now()->format('l'));
        $todayRow = $hours->firstWhere('day', $today);
        if ($todayRow) {
            $from = $todayRow->from instanceof \Carbon\Carbon ? $todayRow->from->format('g:i A') : \Carbon\Carbon::parse($todayRow->from)->format('g:i A');
            $to = $todayRow->to instanceof \Carbon\Carbon ? $todayRow->to->format('g:i A') : \Carbon\Carbon::parse($todayRow->to)->format('g:i A');
            return $from . ' - ' . $to;
        }
        $first = $hours->first();
        $from = $first->from instanceof \Carbon\Carbon ? $first->from->format('g:i A') : \Carbon\Carbon::parse($first->from)->format('g:i A');
        $to = $first->to instanceof \Carbon\Carbon ? $first->to->format('g:i A') : \Carbon\Carbon::parse($first->to)->format('g:i A');
        return $from . ' - ' . $to;
    }

    /**
     * Get the first available appointment slot for a specific doctor at this clinic in the next 14 days.
     * Uses per-doctor working hours when set; filters booked slots by this doctor.
     */
    public function getFirstAvailableSlotForDoctor(Doctor $doctor): ?array
    {
        $start = Carbon::today();
        $doctorWh = $this->clinicDoctorWorkingHours()->where('doctor_id', $doctor->id)->get();
        $whByDay = $doctorWh->isNotEmpty()
            ? $doctorWh->keyBy('day')
            : $this->workingHours()->get()->keyBy('day');

        $booked = $this->appointments()
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', '>=', $start)
            ->where('appointment_date', '<=', $start->copy()->addDays(14))
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->groupBy(function ($a) {
                $d = $a->appointment_date instanceof \Carbon\Carbon ? $a->appointment_date->format('Y-m-d') : $a->appointment_date;
                $t = $a->appointment_time instanceof \Carbon\Carbon ? $a->appointment_time->format('H:i') : \Carbon\Carbon::parse($a->appointment_time)->format('H:i');
                return $d . ' ' . $t;
            })
            ->map->count();

        $cap = $this->getSlotsPerInterval();
        for ($i = 0; $i < 14; $i++) {
            $date = $start->copy()->addDays($i);
            if ($doctor->isOffOnDate($date->format('Y-m-d'), $this->id)) {
                continue;
            }
            $dayName = strtolower($date->format('l'));
            $wh = $whByDay->get($dayName);
            if (!$wh || $wh->is_closed || !$wh->from || !$wh->to) {
                continue;
            }
            $from = $wh->from instanceof \Carbon\Carbon ? $wh->from : \Carbon\Carbon::parse($wh->from);
            $to = $wh->to instanceof \Carbon\Carbon ? $wh->to : \Carbon\Carbon::parse($wh->to);
            $current = $from->copy();
            $dateStr = $date->format('Y-m-d');
            while ($current->format('H:i') < $to->format('H:i')) {
                $timeStr = $current->format('H:i');
                $key = $dateStr . ' ' . $timeStr;
                if (($booked->get($key) ?? 0) < $cap) {
                    $display = $date->format('M j, Y') . ' ' . $current->format('g:i A');
                    return ['date' => $dateStr, 'time' => $timeStr, 'display' => $display];
                }
                $current->addMinutes(30);
            }
        }
        return null;
    }

    /**
     * Get available days for a specific doctor at this clinic (for booking form).
     */
    public function getAvailableDaysForDoctor(Doctor $doctor, int $limit = 14): array
    {
        $start = Carbon::today();
        $doctorWh = $this->clinicDoctorWorkingHours()->where('doctor_id', $doctor->id)->get();
        $whByDay = $doctorWh->isNotEmpty()
            ? $doctorWh->keyBy('day')
            : $this->workingHours()->get()->keyBy('day');

        $booked = $this->appointments()
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', '>=', $start)
            ->where('appointment_date', '<=', $start->copy()->addDays($limit))
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->groupBy(function ($a) {
                $d = $a->appointment_date instanceof \Carbon\Carbon ? $a->appointment_date->format('Y-m-d') : $a->appointment_date;
                $t = $a->appointment_time instanceof \Carbon\Carbon ? $a->appointment_time->format('H:i') : \Carbon\Carbon::parse($a->appointment_time)->format('H:i');
                return $d . ' ' . $t;
            })
            ->map->count();

        $cap = $this->getSlotsPerInterval();
        $available = [];
        for ($i = 0; $i < $limit; $i++) {
            $date = $start->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            if ($doctor->isOffOnDate($dateStr, $this->id)) {
                continue;
            }
            $dayName = strtolower($date->format('l'));
            $wh = $whByDay->get($dayName);
            if (!$wh || $wh->is_closed || !$wh->from || !$wh->to) {
                continue;
            }
            $from = $wh->from instanceof \Carbon\Carbon ? $wh->from : \Carbon\Carbon::parse($wh->from);
            $to = $wh->to instanceof \Carbon\Carbon ? $wh->to : \Carbon\Carbon::parse($wh->to);
            $current = $from->copy();
            $hasSlot = false;
            while ($current->format('H:i') < $to->format('H:i')) {
                $timeStr = $current->format('H:i');
                if (($booked->get($dateStr . ' ' . $timeStr) ?? 0) < $cap) {
                    $hasSlot = true;
                    break;
                }
                $current->addMinutes(30);
            }
            if ($hasSlot) {
                $available[] = ['date' => $dateStr, 'label' => $date->format('D, M j')];
            }
        }
        return $available;
    }

    /**
     * Get examination/follow-up price for a specific doctor at this clinic (pivot or clinic default).
     */
    public function getPriceForDoctor(Doctor $doctor, string $type = 'medical_examination'): string|float
    {
        $pivot = $this->doctors()->where('doctors.id', $doctor->id)->first()?->pivot;
        if ($pivot) {
            $val = $type === 'medical_examination' ? $pivot->medical_examination_price : $pivot->follow_up_price;
            if ($val !== null && $val !== '') {
                return (float) $val;
            }
        }
        return $type === 'medical_examination' ? (float) $this->medical_examination_price : (float) $this->follow_up_price;
    }
}
