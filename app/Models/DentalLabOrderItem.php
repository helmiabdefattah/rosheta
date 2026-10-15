<?php

namespace App\Models;

use App\Enums\DentalItemLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line on a lab order. level / name / unit are snapshots from the catalog at
 * order time; price is entered per line. Line total = quantity × unit price.
 */
class DentalLabOrderItem extends Model
{
    protected $fillable = [
        'dental_lab_order_id',
        'dental_item_id',
        'level',
        'name',
        'unit',
        'quantity',
        'unit_price',
        'operating_fee',
        'teeth',
        'used_at',
    ];

    protected $casts = [
        'level' => DentalItemLevel::class,
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'operating_fee' => 'decimal:2',
        'teeth' => 'array',
        'used_at' => 'datetime',
    ];

    /** A material line the doctor has consumed from stock. */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /** Only clinic-general material lines are tracked in (and used from) stock. */
    public function tracksStock(): bool
    {
        return $this->level === DentalItemLevel::ClinicGeneral;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(DentalLabOrder::class, 'dental_lab_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(DentalItem::class, 'dental_item_id');
    }

    /** Line total (lab price): quantity × unit price. */
    public function total(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }

    /** This line's operating fee. */
    public function operatingFee(): float
    {
        return round((float) ($this->operating_fee ?? 0), 2);
    }

    /** What the patient pays for this line: lab total + operating fee. */
    public function patientTotal(): float
    {
        return round($this->total() + $this->operatingFee(), 2);
    }

    /**
     * The teeth that actually apply to this line: its own when the order is in
     * per-item mode, otherwise the order-level selection.
     *
     * @return array<int, string>
     */
    public function effectiveTeeth(): array
    {
        if ($this->level !== DentalItemLevel::Tooth) {
            return [];
        }

        if ($this->order && $this->order->teeth_mode === 'item') {
            return $this->teeth ?? [];
        }

        return $this->order?->teeth ?? [];
    }
}
