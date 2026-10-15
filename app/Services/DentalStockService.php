<?php

namespace App\Services;

use App\Enums\DentalItemLevel;
use App\Enums\StockMovementType;
use App\Models\DentalLabOrder;
use App\Models\DentalLabOrderItem;
use App\Models\DentalStockItem;
use Illuminate\Support\Facades\DB;

/**
 * Keeps clinic material stock in step with lab orders.
 *
 * A lab order is a purchase from the lab, so when it is received the clinic-general
 * materials on it ARRIVE at the clinic and are added to stock (one "in" movement
 * each, linked back to the order). The link lets the receipt be reversed cleanly
 * if the order leaves "received" or is deleted, and makes the whole thing
 * idempotent — re-applying does nothing.
 */
class DentalStockService
{
    /** Add an order's clinic-general materials to stock, once. */
    public function applyOrderReceipt(DentalLabOrder $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($this->materialLines($order) as $line) {
                // Idempotent: skip a line already received into stock for this order.
                if ($this->hasMovementFor($order, $line)) {
                    continue;
                }

                $stock = $this->resolveStockItem($order, $line);
                $qty = (float) $line->quantity;
                if ($qty <= 0) {
                    continue;
                }

                $stock->recordMovement(StockMovementType::In, $qty, [
                    'reason' => __('app.dental.stock.received_from_order', ['id' => $order->id]),
                    'source' => $order,
                ]);
            }
        });
    }

    /** Undo an order's stock receipt (order left "received" or is being deleted). */
    public function reverseOrderReceipt(DentalLabOrder $order): void
    {
        DB::transaction(function () use ($order) {
            // Reverse every stock movement tied to this order, whatever its
            // direction, by recording its exact opposite — so an order recovers
            // cleanly even if older movements were in the other direction.
            $movements = \App\Models\DentalStockMovement::where('source_type', $order->getMorphClass())
                ->where('source_id', $order->getKey())
                ->get();

            foreach ($movements as $movement) {
                $stock = $movement->stockItem;
                if (! $stock) {
                    continue;
                }

                $qty = (float) $movement->quantity;
                if ($qty === 0.0) {
                    continue;
                }

                // Opposite sign cancels the original movement.
                $type = $qty > 0 ? StockMovementType::Out : StockMovementType::In;
                $stock->recordMovement($type, -$qty, [
                    'reason' => __('app.dental.stock.reversed_order', ['id' => $order->id]),
                    'source' => $order,
                ]);
            }
        });
    }

    /**
     * Mark one received material line as used: the doctor consumed it on the
     * patient, so it leaves clinic stock. Stamps used_at and records a stock-out
     * linked to the order. No-op for non-material lines or already-used ones.
     */
    public function markItemUsed(DentalLabOrderItem $item): void
    {
        if (! $item->tracksStock() || $item->isUsed()) {
            return;
        }

        DB::transaction(function () use ($item) {
            $order = $item->order;
            $qty = (float) $item->quantity;

            if ($order && $qty > 0) {
                $stock = $this->resolveStockItem($order, $item);
                $stock->recordMovement(StockMovementType::Out, -$qty, [
                    'reason' => __('app.dental.stock.used_by_order', ['id' => $order->id]),
                    'source' => $order,
                ]);
            }

            $item->forceFill(['used_at' => now()])->save();
        });
    }

    /** Undo a used mark: return the line's quantity to stock. */
    public function unmarkItemUsed(DentalLabOrderItem $item): void
    {
        if (! $item->isUsed()) {
            return;
        }

        DB::transaction(function () use ($item) {
            $order = $item->order;
            $qty = (float) $item->quantity;

            if ($order && $item->tracksStock() && $qty > 0) {
                $stock = $this->resolveStockItem($order, $item);
                $stock->recordMovement(StockMovementType::In, $qty, [
                    'reason' => __('app.dental.stock.returned_to_stock', ['id' => $order->id]),
                    'source' => $order,
                ]);
            }

            $item->forceFill(['used_at' => null])->save();
        });
    }

    /** @return \Illuminate\Support\Collection<int, DentalLabOrderItem> */
    private function materialLines(DentalLabOrder $order): \Illuminate\Support\Collection
    {
        return $order->items->filter(
            fn (DentalLabOrderItem $i) => $i->level === DentalItemLevel::ClinicGeneral
        )->values();
    }

    private function hasMovementFor(DentalLabOrder $order, DentalLabOrderItem $line): bool
    {
        return \App\Models\DentalStockMovement::where('source_type', $order->getMorphClass())
            ->where('source_id', $order->getKey())
            ->where('type', StockMovementType::In->value)
            ->whereHas('stockItem', function ($q) use ($line) {
                $q->where(function ($q) use ($line) {
                    if ($line->dental_item_id) {
                        $q->where('dental_item_id', $line->dental_item_id);
                    } else {
                        $q->where('name', $line->name)->where('unit', $line->unit);
                    }
                });
            })
            ->exists();
    }

    /**
     * Find the clinic stock row for a material line — by catalog item, else by
     * name + unit — creating one (starting at zero) if the material isn't tracked
     * yet, so the ledger always reflects what was actually received.
     */
    private function resolveStockItem(DentalLabOrder $order, DentalLabOrderItem $line): DentalStockItem
    {
        $query = DentalStockItem::where('clinic_id', $order->clinic_id);

        if ($line->dental_item_id) {
            $query->where('dental_item_id', $line->dental_item_id);
        } else {
            $query->where('name', $line->name)->where('unit', $line->unit);
        }

        return $query->first() ?? DentalStockItem::create([
            'clinic_id' => $order->clinic_id,
            'dental_item_id' => $line->dental_item_id,
            'name' => $line->name,
            'unit' => $line->unit,
            'quantity_on_hand' => 0,
            'is_active' => true,
        ]);
    }
}
