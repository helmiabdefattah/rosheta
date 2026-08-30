<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A note pinned to a part of the body on a patient's clinical chart.
 * See the create migration for why these hang off the patient, not the visit.
 */
class ClinicalChartNote extends Model
{
    protected $fillable = [
        'client_id', 'appointment_id', 'doctor_id',
        'chart', 'region', 'point_x', 'point_y', 'note',
    ];

    protected $casts = [
        'point_x' => 'float',
        'point_y' => 'float',
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

    /** True when this note carries a pinned position rather than a whole part. */
    public function hasPoint(): bool
    {
        return $this->point_x !== null && $this->point_y !== null;
    }
}
