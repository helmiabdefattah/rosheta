<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalPlanItem extends Model
{
    protected $fillable = [
        'medical_plan_id', 'medicine_name', 'dose', 'frequency', 'duration', 'instructions',
        'substitute_name', 'substitute_dose', 'substitute_frequency', 'substitute_duration', 'substitute_instructions',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MedicalPlan::class, 'medical_plan_id');
    }
}
