<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Growth measurements taken at one paediatric visit. Anchored to the patient so
 * the growth history reads across visits; see the create migration.
 */
class PediatricVisitRecord extends Model
{
    protected $fillable = [
        'client_id', 'appointment_id', 'doctor_id',
        'weight_kg', 'height_cm', 'head_circumference_cm', 'temperature_c',
        'notes', 'recorded_at',
    ];

    protected $casts = [
        'weight_kg' => 'float',
        'height_cm' => 'float',
        'head_circumference_cm' => 'float',
        'temperature_c' => 'float',
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
