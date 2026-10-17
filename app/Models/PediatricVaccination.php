<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One dose in a child's vaccination log. Anchored to the patient; see the
 * create migration.
 */
class PediatricVaccination extends Model
{
    protected $fillable = [
        'client_id', 'clinic_id', 'doctor_id', 'appointment_id',
        'vaccine', 'dose', 'given_at', 'due_at', 'notes',
    ];

    protected $casts = [
        'given_at' => 'date',
        'due_at' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
