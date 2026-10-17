<?php

namespace App\Models;

use App\Enums\ExpensePaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One instalment paid against an expense. The expense's status is recomputed
 * from the sum of its payments whenever one is added or removed.
 */
class ExpensePayment extends Model
{
    protected $fillable = [
        'expense_id',
        'amount',
        'paid_at',
        'method',
        'paid_by',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'method' => ExpensePaymentMethod::class,
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /** The staff user who recorded the payment. */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
