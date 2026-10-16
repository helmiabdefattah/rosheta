<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A woman's current pregnancy. The dating is set once; gestational age and the
 * expected delivery date are computed from it, for any date, so the file always
 * shows today's age. See the create migration for the two dating methods.
 */
class PregnancyRecord extends Model
{
    /** A full-term pregnancy, in days (40 weeks), for the EDD. */
    public const TERM_DAYS = 280;

    protected $fillable = [
        'client_id', 'doctor_id', 'dating_method',
        'lmp_date', 'ga_assessed_on', 'ga_weeks', 'ga_days',
        'gravida', 'para', 'blood_group', 'notes', 'is_active',
    ];

    protected $casts = [
        'lmp_date' => 'date',
        'ga_assessed_on' => 'date',
        'is_active' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** The date the dating is measured from: the LMP, or the manual reference date. */
    public function anchorDate(): ?Carbon
    {
        $date = $this->dating_method === 'manual' ? $this->ga_assessed_on : $this->lmp_date;

        return $date ? $date->copy()->startOfDay() : null;
    }

    /** Gestational age, in days, at the anchor date (0 for an LMP anchor). */
    public function anchorGaDays(): int
    {
        if ($this->dating_method === 'manual') {
            return (int) $this->ga_weeks * 7 + (int) $this->ga_days;
        }

        return 0;
    }

    /**
     * Gestational age in days on a given date (today by default): the anchor age
     * plus whole days elapsed since the anchor. Null when no dating is set.
     */
    public function gestationalAgeDays(Carbon|string|null $on = null): ?int
    {
        $anchor = $this->anchorDate();
        if (! $anchor) {
            return null;
        }

        $on = $on ? Carbon::parse($on)->startOfDay() : Carbon::today();
        // Signed, integer day delta — robust across Carbon 2/3 diff defaults.
        $elapsed = (int) $anchor->diffInDays($on, false);

        return max(0, $this->anchorGaDays() + $elapsed);
    }

    /** Expected delivery date: full term measured back from the same anchor. */
    public function expectedDeliveryDate(): ?Carbon
    {
        $anchor = $this->anchorDate();
        if (! $anchor) {
            return null;
        }

        return $anchor->copy()->addDays(self::TERM_DAYS - $this->anchorGaDays());
    }
}
