<?php

namespace App\Models;

use App\Enums\RecurrenceInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A template for an expense that repeats. The generator turns it into real
 * Expense rows as each `next_run_on` falls due.
 */
class RecurringExpense extends Model
{
    protected $fillable = [
        'clinic_id',
        'expense_category_id',
        'vendor_id',
        'title',
        'amount',
        'tax_amount',
        'interval',
        'next_run_on',
        'last_generated_on',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'interval' => RecurrenceInterval::class,
        'next_run_on' => 'date',
        'last_generated_on' => 'date',
        'is_active' => 'boolean',
    ];

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

    public function scopeDue(Builder $query, ?string $onDate = null): Builder
    {
        return $query->where('is_active', true)
            ->whereDate('next_run_on', '<=', $onDate ?? now()->toDateString());
    }
}
