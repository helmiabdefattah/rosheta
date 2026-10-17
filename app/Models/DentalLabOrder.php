<?php

namespace App\Models;

use App\Enums\DentalLabOrderStatus;
use App\Enums\DentalPaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A work order from a clinic to a dental lab. Totals are derived from the line
 * items; the payment standing is derived from the payments against that total.
 */
class DentalLabOrder extends Model
{
    protected $fillable = [
        'clinic_id',
        'dental_lab_id',
        'client_id',
        'status',
        'teeth_mode',
        'teeth',
        'notes',
        'patient_price',
        'operating_fee',
        'received_at',
        'created_by',
    ];

    protected $casts = [
        'status' => DentalLabOrderStatus::class,
        'teeth' => 'array',
        'patient_price' => 'decimal:2',
        'operating_fee' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(DentalLab::class, 'dental_lab_id');
    }

    /** The patient (clients table); null for material-only orders. */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DentalLabOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DentalLabPayment::class);
    }

    /** Payments the patient made to the clinic for this order (clinic receivable). */
    public function patientPayments(): HasMany
    {
        return $this->hasMany(DentalPatientPayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Money ──────────────────────────────────────────────────────────────

    /** Order total = sum of line totals. */
    public function total(): float
    {
        return round($this->items->sum(fn (DentalLabOrderItem $i) => $i->total()), 2);
    }

    /** Everything paid to the lab so far. */
    public function paidTotal(): float
    {
        return round($this->payments->sum('amount'), 2);
    }

    /** What is still owed (never negative). */
    public function balance(): float
    {
        return round(max($this->total() - $this->paidTotal(), 0), 2);
    }

    /** Derived unpaid / partial / paid standing. */
    public function paymentStatus(): DentalPaymentStatus
    {
        $total = $this->total();
        $paid = $this->paidTotal();

        if ($paid <= 0) {
            return DentalPaymentStatus::Unpaid;
        }

        // Treat as fully paid once what's paid covers the total (handles rounding).
        return $paid + 0.001 >= $total && $total > 0
            ? DentalPaymentStatus::Paid
            : DentalPaymentStatus::Partial;
    }

    // ── Patient side (clinic receivable) ─────────────────────────────────────

    /** The clinic's operating fee for the whole order (sum of the line fees). */
    public function operatingFee(): float
    {
        return round($this->items->sum(fn (DentalLabOrderItem $i) => $i->operatingFee()), 2);
    }

    /**
     * What the clinic charges the patient = the lab price (order total) plus the
     * operating fee, for orders that have a patient. 0 otherwise.
     */
    public function patientCharge(): float
    {
        $base = $this->client_id ? $this->total() : 0.0;

        return round($base + $this->operatingFee(), 2);
    }

    /** Whether this order is billed to a patient. */
    public function isPatientBilled(): bool
    {
        return $this->client_id !== null || $this->operatingFee() > 0;
    }

    /** What the patient has paid the clinic so far. */
    public function patientPaidTotal(): float
    {
        return round($this->patientPayments->sum('amount'), 2);
    }

    /** What the patient still owes the clinic for this order. */
    public function patientBalance(): float
    {
        return round(max($this->patientCharge() - $this->patientPaidTotal(), 0), 2);
    }

    /** Derived patient payment standing. */
    public function patientPaymentStatus(): DentalPaymentStatus
    {
        $charge = $this->patientCharge();
        $paid = $this->patientPaidTotal();

        if ($paid <= 0) {
            return DentalPaymentStatus::Unpaid;
        }

        return $paid + 0.001 >= $charge && $charge > 0
            ? DentalPaymentStatus::Paid
            : DentalPaymentStatus::Partial;
    }

    /** True while the order is still in progress (not yet received or used). */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            DentalLabOrderStatus::Received->value,
            DentalLabOrderStatus::Used->value,
        ]);
    }
}
