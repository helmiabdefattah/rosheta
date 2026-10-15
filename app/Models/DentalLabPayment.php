<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single payment from the clinic to the lab for an order. Many per order, so
 * an order can be settled in instalments.
 */
class DentalLabPayment extends Model
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
