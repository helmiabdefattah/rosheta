<?php

namespace App\Models;

use App\Enums\VendorType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A supplier the clinic pays (lab, landlord, utility, …). Its balance is the
 * opening balance plus everything billed to it, less everything paid against
 * those expenses.
 */
class Vendor extends Model
{
    protected $fillable = [
        'clinic_id',
        'dental_lab_id',
        'name',
        'type',
        'phone',
        'email',
        'opening_balance',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'type' => VendorType::class,
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /** The dental lab this vendor represents, when linked. */
    public function dentalLab(): BelongsTo
    {
        return $this->belongsTo(DentalLab::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /** Total billed to this vendor (gross of its expenses). */
    public function totalBilled(): float
    {
        $expenses = $this->relationLoaded('expenses') ? $this->expenses : $this->expenses()->with('payments')->get();

        return round($expenses->sum(fn (Expense $e) => $e->gross()), 2);
    }

    /** Total paid against this vendor's expenses. */
    public function totalPaid(): float
    {
        $expenses = $this->relationLoaded('expenses') ? $this->expenses : $this->expenses()->with('payments')->get();

        return round($expenses->sum(fn (Expense $e) => $e->paidTotal()), 2);
    }

    /** Opening balance + billed − paid: what the clinic still owes this vendor. */
    public function balance(): float
    {
        return round((float) $this->opening_balance + $this->totalBilled() - $this->totalPaid(), 2);
    }

    public function scopeForClinic(Builder $query, int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }
}
