<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The insurance split for one appointment: patient_amount out of pocket +
 * insurance_amount claimed from insurance_company.
 */
class AppointmentInsurance extends Model
{
    protected $fillable = [
        'appointment_id',
        'insurance_company_id',
        'patient_amount',
        'insurance_amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'patient_amount' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }
}
