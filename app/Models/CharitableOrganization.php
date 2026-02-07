<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharitableOrganization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'governorate_id',
        'city_id',
        'area_id',
        'phone_numbers',
        'services',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'services' => 'array',
    ];

    /**
     * Get the governorate that owns the charitable organization.
     */
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    /**
     * Get the city that owns the charitable organization.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the area that owns the charitable organization.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
