<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A reusable prescription template (titled set of medicines).
 */
class MedicalPlan extends Model
{
    protected $fillable = [
        'doctor_id',
        'title',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicalPlanItem::class);
    }
}
