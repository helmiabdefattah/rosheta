<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A payout received from an insurance company (recorded manually).
 */
class InsuranceCollection extends Model
{
    protected $fillable = [
        'doctor_id',
        'insurance_company_id',
        'amount',
        'collected_on',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_on' => 'date',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
