<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A clinic's stock of a dental material/consumable. quantity_on_hand is kept in
 * step with the movement ledger through recordMovement(); reorder_level drives
 * the low-stock warning.
 */
class DentalStockItem extends Model
{
    protected $fillable = [
        'clinic_id',
        'dental_item_id',
        'name',
        'unit',
        'quantity_on_hand',
        'reorder_level',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(DentalItem::class, 'dental_item_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(DentalStockMovement::class)->latest('happened_at');
    }

    /** At or below the reorder level (only when a level is set). */
    public function isLow(): bool
    {
        return $this->reorder_level !== null
            && (float) $this->quantity_on_hand <= (float) $this->reorder_level;
    }

    /**
     * Record a stock change and keep quantity_on_hand in step, atomically.
     * $signedQuantity is positive for stock in, negative for stock out.
     *
     * @param  array{reason?: string|null, source?: Model|null, created_by?: int|null, happened_at?: \DateTimeInterface|string|null}  $opts
     */
    public function recordMovement(StockMovementType $type, float $signedQuantity, array $opts = []): DentalStockMovement
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($type, $signedQuantity, $opts) {
            $movement = new DentalStockMovement([
                'clinic_id' => $this->clinic_id,
                'type' => $type,
                'quantity' => $signedQuantity,
                'reason' => $opts['reason'] ?? null,
                'created_by' => $opts['created_by'] ?? auth()->id(),
                'happened_at' => $opts['happened_at'] ?? now(),
            ]);

            if (! empty($opts['source']) && $opts['source'] instanceof Model) {
                $movement->source()->associate($opts['source']);
            }

            $this->movements()->save($movement);

            $this->increment('quantity_on_hand', $signedQuantity);

            return $movement;
        });
    }

    public function scopeForClinic(Builder $query, int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }
}
