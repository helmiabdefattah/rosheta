<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\DentalItemLevel;
use App\Enums\StockMovementType;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Clinic\Concerns\DentalExports;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\DentalStockAdjustRequest;
use App\Http\Requests\Clinic\DentalStockItemRequest;
use App\Models\Clinic;
use App\Models\DentalItem;
use App\Models\DentalStockItem;
use App\Models\DentalStockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Clinic materials stock: items with on-hand quantities, a low-stock warning,
 * manual adjustments, and the movement ledger.
 */
class DentalStockController extends Controller
{
    use ClinicContext;
    use DentalExports;

    /**
     * Export the current materials stock (on-hand levels) as PDF or Excel (CSV).
     * ?format=pdf|excel. Stock is a snapshot, so there is no date range.
     */
    public function export(Request $request)
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $items = DentalStockItem::forClinic($clinic->id)->orderBy('name')->get();

        $columns = [
            __('app.dental.stock.name'),
            __('app.dental.stock.unit'),
            __('app.dental.stock.on_hand'),
            __('app.dental.stock.reorder'),
            __('app.dental.lab.status'),
        ];

        $rows = $items->map(fn (DentalStockItem $i) => [
            $i->name,
            $i->unit,
            rtrim(rtrim(number_format((float) $i->quantity_on_hand, 2), '0'), '.'),
            $i->reorder_level !== null ? rtrim(rtrim(number_format((float) $i->reorder_level, 2), '0'), '.') : '—',
            $i->isLow() ? __('app.dental.stock.low') : __('app.dental.common.active'),
        ])->all();

        return $this->exportResponse(
            $request, $clinic, 'stock',
            __('app.dental.export.stock_title'), now()->format('Y-m-d'), $columns, $rows
        );
    }

    public function index(Request $request): View
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $items = DentalStockItem::forClinic($clinic->id)
            ->with('item')
            ->orderBy('name')
            ->get();

        $movements = DentalStockMovement::where('clinic_id', $clinic->id)
            ->with(['stockItem', 'creator', 'source' => fn (\Illuminate\Database\Eloquent\Relations\MorphTo $m) => $m->morphWith([
                \App\Models\DentalLabOrder::class => ['lab'],
            ])])
            ->latest('happened_at')
            ->limit(50)
            ->get();

        // Clinic-general catalog items, for linking a stock row to a material.
        $materials = DentalItem::availableFor($clinic->id)
            ->where('level', DentalItemLevel::ClinicGeneral->value)
            ->get();

        // Patient work received back from the lab (ready to deliver / fit).
        $receivedOrders = \App\Models\DentalLabOrder::where('clinic_id', $clinic->id)
            ->where('status', \App\Enums\DentalLabOrderStatus::Received->value)
            ->whereNotNull('client_id')
            ->with(['patient', 'lab', 'items'])
            ->latest('received_at')
            ->get();

        return view('clinic.dental-lab.stock.index', compact('clinic', 'items', 'movements', 'materials', 'receivedOrders'));
    }

    public function store(DentalStockItemRequest $request): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        // Promote a newly-typed material into the catalog so it becomes a reusable
        // option next time; reuse the catalog entry when the name already exists.
        $dentalItemId = $this->resolveMaterial($clinic, $request->input('dental_item_id'), $request->input('name'), $request->input('unit'));

        $item = DentalStockItem::create([
            'clinic_id' => $clinic->id,
            'dental_item_id' => $dentalItemId,
            'name' => $request->input('name'),
            'unit' => $request->input('unit'),
            'quantity_on_hand' => 0,
            'reorder_level' => $request->input('reorder_level'),
            'is_active' => $request->boolean('is_active', true),
            'notes' => $request->input('notes'),
        ]);

        // Record the opening balance as a movement so the ledger stays truthful.
        $opening = (float) $request->input('opening_quantity', 0);
        if ($opening > 0) {
            $item->recordMovement(StockMovementType::In, $opening, [
                'reason' => __('app.dental.stock.opening_balance'),
            ]);
        }

        return back()->with('status', __('app.dental.stock.item_created'));
    }

    public function update(DentalStockItemRequest $request, DentalStockItem $stockItem): RedirectResponse
    {
        $this->guardStock($request, $stockItem);

        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $dentalItemId = $this->resolveMaterial($clinic, $request->input('dental_item_id'), $request->input('name'), $request->input('unit'));

        // Quantity is changed through adjustments, never edited here directly.
        $stockItem->update([
            'dental_item_id' => $dentalItemId,
            'name' => $request->input('name'),
            'unit' => $request->input('unit'),
            'reorder_level' => $request->input('reorder_level'),
            'is_active' => $request->boolean('is_active', true),
            'notes' => $request->input('notes'),
        ]);

        return back()->with('status', __('app.dental.stock.item_updated'));
    }

    public function adjust(DentalStockAdjustRequest $request, DentalStockItem $stockItem): RedirectResponse
    {
        $this->guardStock($request, $stockItem);

        $type = StockMovementType::from($request->input('type'));
        $qty = (float) $request->input('quantity');

        // In = +qty, Out = -qty, Adjust = stock-take that SETS the on-hand to qty.
        $signed = match ($type) {
            StockMovementType::In => $qty,
            StockMovementType::Out => -$qty,
            StockMovementType::Adjust => round($qty - (float) $stockItem->quantity_on_hand, 2),
        };

        if ($signed !== 0.0) {
            $stockItem->recordMovement($type, $signed, [
                'reason' => $request->input('reason'),
            ]);
        }

        return back()->with('status', __('app.dental.stock.adjusted'));
    }

    /**
     * Resolve the catalog material for a stock row: the chosen one, an existing
     * clinic-general item with the same name, or a brand-new clinic-general item
     * created from the typed name (so it becomes a reusable option).
     */
    private function resolveMaterial(Clinic $clinic, $dentalItemId, ?string $name, ?string $unit): ?int
    {
        if ($dentalItemId) {
            return (int) $dentalItemId;
        }
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $existing = DentalItem::where('level', DentalItemLevel::ClinicGeneral->value)
            ->where(fn ($q) => $q->whereNull('clinic_id')->orWhere('clinic_id', $clinic->id))
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            return $existing->id;
        }

        return DentalItem::create([
            'clinic_id' => $clinic->id,
            'level' => DentalItemLevel::ClinicGeneral->value,
            'name' => $name,
            'default_unit' => $unit ?: 'piece',
            'is_active' => true,
        ])->id;
    }

    private function guardStock(Request $request, DentalStockItem $stockItem): Clinic
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($stockItem->clinic_id === $clinic->id, 403);

        return $clinic;
    }
}
