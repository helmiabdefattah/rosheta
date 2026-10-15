<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One entry in a stock item's ledger. quantity is signed (+in / -out); source
 * optionally links the movement to what caused it (e.g. a received lab order),
 * so that cause can be reversed cleanly.
 */
class DentalStockMovement extends Model
{
    protected $fillable = [
        'dental_stock_item_id',
        'clinic_id',
        'type',
        'quantity',
        'reason',
        'created_by',
        'happened_at',
    ];

    protected $casts = [
        'type' => StockMovementType::class,
        'quantity' => 'decimal:2',
        'happened_at' => 'datetime',
    ];

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(DentalStockItem::class, 'dental_stock_item_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
