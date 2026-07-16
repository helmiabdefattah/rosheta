<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One chargeable line on a visit. `name` / `unit_price` are snapshots from the
 * moment it was added, so later catalog edits don't rewrite historical bills.
 */
class AppointmentItem extends Model
{
    protected $fillable = [
        'appointment_id',
        'billable_item_id',
        'name',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function billableItem(): BelongsTo
    {
        return $this->belongsTo(BillableItem::class);
    }

    /** Line total: quantity × unit price. */
    public function total(): float
    {
        return round($this->quantity * (float) $this->unit_price, 2);
    }
}
