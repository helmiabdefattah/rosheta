<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pharmacy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'area_id',
        'name',
        'phone',
        'email',
        'address',
        'location',
        'lat',
        'lng',
        'license_number',
        'pharmacist_name',
        'pharmacist_license',
        'opening_time',
        'closing_time',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the pharmacy.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the area that the pharmacy belongs to.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
