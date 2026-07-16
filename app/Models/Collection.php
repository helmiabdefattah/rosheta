<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One payment taken from a patient at the front desk for an appointment.
 * An appointment may have several (part-payments); the visit is settled when
 * they add up to the appointment's price.
 */
class Collection extends Model
{
    protected $fillable = [
        'appointment_id',
        'amount',
        'collected_by',
        'collected_at',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** The staff user who took the payment. */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
