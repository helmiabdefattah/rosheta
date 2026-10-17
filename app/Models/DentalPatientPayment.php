<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A payment the patient makes to the clinic against a lab order (clinic
 * receivable). Separate from DentalLabPayment (clinic → lab).
 */
class DentalPatientPayment extends Model
{
    protected $fillable = [
        'dental_lab_order_id',
        'amount',
        'paid_at',
        'method',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(DentalLabOrder::class, 'dental_lab_order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
