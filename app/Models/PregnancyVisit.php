<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Antenatal follow-up data for one visit (mother + fetus). Anchored to the
 * patient; see the create migration.
 */
class PregnancyVisit extends Model
{
    protected $fillable = [
        'client_id', 'appointment_id', 'doctor_id', 'ga_days',
        'weight_kg', 'blood_pressure', 'fundal_height_cm', 'fetal_heart_rate',
        'presentation', 'fetal_weight_g', 'fetus_count', 'ultrasound', 'notes',
        'recorded_at',
    ];

    protected $casts = [
        'weight_kg' => 'float',
        'fundal_height_cm' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
