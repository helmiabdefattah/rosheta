<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * A clinic expense. The gross cost is `amount` + `tax_amount`; the
 * expense_payments ledger settles it and drives `status`. Receipts/invoices
 * are attached to the `receipts` media collection.
 */
class Expense extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'expense_category_id',
        'vendor_id',
        'created_by',
        'title',
        'description',
        'amount',
        'tax_amount',
        'expense_date',
        'status',
        'reference_no',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'expense_date' => 'date',
        'status' => ExpenseStatus::class,
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('receipts');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ExpensePayment::class);
    }

    /** What caused this expense (e.g. a dental lab payout), when linked. */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /** Net + tax: the full amount the payments ledger needs to cover. */
    public function gross(): float
    {
        return round((float) $this->amount + (float) $this->tax_amount, 2);
    }

    /** Total paid so far across the payments ledger. */
    public function paidTotal(): float
    {
        // Use the loaded relation when present to avoid an extra query in lists.
        $payments = $this->relationLoaded('payments') ? $this->payments : $this->payments();

        return round((float) $payments->sum('amount'), 2);
    }

    /** Still owed (never negative). */
    public function balance(): float
    {
        return round(max(0, $this->gross() - $this->paidTotal()), 2);
    }

    /** Recompute `status` from the payments ledger. Saves unless told not to. */
    public function recomputeStatus(bool $save = true): ExpenseStatus
    {
        $paid = $this->paidTotal();
        $gross = $this->gross();

        $status = match (true) {
            $paid <= 0 => ExpenseStatus::Unpaid,
            $paid + 0.001 >= $gross => ExpenseStatus::Paid,
            default => ExpenseStatus::Partial,
        };

        if ($this->status !== $status) {
            $this->status = $status;
            if ($save) {
                $this->save();
            }
        }

        return $status;
    }

    /** Scope to a clinic. */
    public function scopeForClinic(Builder $query, int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }

    /** Scope to an inclusive expense_date range (Y-m-d bounds). */
    public function scopeInPeriod(Builder $query, string $from, string $to): Builder
    {
        return $query->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to);
    }
}
