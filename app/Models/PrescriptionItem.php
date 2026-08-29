<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id', 'medicine_name', 'dose', 'frequency', 'duration', 'instructions',
        // The optional alternative carries the same detail as the primary medicine.
        'substitute_name', 'substitute_dose', 'substitute_frequency', 'substitute_duration', 'substitute_instructions',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
