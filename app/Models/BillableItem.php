<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An entry in a doctor's price list of chargeable extras. Shared across every
 * clinic the doctor works in.
 */
class BillableItem extends Model
{
    protected $fillable = ['doctor_id', 'name', 'price', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointmentItems(): HasMany
    {
        return $this->hasMany(AppointmentItem::class);
    }
}
