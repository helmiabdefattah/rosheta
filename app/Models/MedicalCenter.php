<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A medical center: one location/brand grouping several clinics. Each clinic
 * may or may not have a doctor assigned.
 */
class MedicalCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'phone_number',
        'governorate_id', 'city_id', 'area_id',
        'latitude', 'longitude',
        'open_time', 'close_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
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

    /** "9:00 AM - 5:00 PM" when both times are set, else null. */
    public function getOpeningHoursSummary(): ?string
    {
        if (! $this->open_time || ! $this->close_time) {
            return null;
        }

        return Carbon::parse($this->open_time)->format('g:i A')
            . ' - ' . Carbon::parse($this->close_time)->format('g:i A');
    }
}
