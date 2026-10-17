<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A monthly budget for a category (or overall, when the category is null).
 */
class ExpenseBudget extends Model
{
    protected $fillable = [
        'clinic_id',
        'expense_category_id',
        'period_month',
        'amount',
    ];

    protected $casts = [
        'period_month' => 'date',
        'amount' => 'decimal:2',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
}
