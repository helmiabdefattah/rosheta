<?php

namespace App\Models;

use App\Enums\DentalItemLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A catalog entry (crown, denture, impression material…) in one of three levels.
 * Shared catalog rows have clinic_id = null; a clinic may add its own on top.
 * No price is stored — it is entered per order line.
 */
class DentalItem extends Model
{
    protected $fillable = [
        'clinic_id',
        'level',
        'name',
        'name_ar',
        'default_unit',
        'is_active',
        'sort',
    ];

    protected $casts = [
        'level' => DentalItemLevel::class,
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /** Localised display name. */
    public function displayName(): string
    {
        return app()->getLocale() === 'ar'
            ? ($this->name_ar ?: $this->name)
            : $this->name;
    }

    /**
     * Catalog available to a clinic: the shared rows plus that clinic's own,
     * active only, in level then sort order.
     */
    public function scopeAvailableFor(Builder $query, int $clinicId): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('clinic_id')->orWhere('clinic_id', $clinicId))
            ->orderBy('level')
            ->orderBy('sort');
    }
}
