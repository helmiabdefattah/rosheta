<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\DentalItemLevel;
use App\Enums\DentalLabOrderStatus;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\DentalLabOrderRequest;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\DentalItem;
use App\Models\DentalLab;
use App\Models\DentalLabOrder;
use App\Models\DentalLabOrderItem;
use App\Services\DentalStockService;
use App\Support\DentalTooth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Dental lab work orders: create with line items, advance the workflow status,
 * and (on receipt) deduct consumed materials from clinic stock.
 */
class DentalLabOrderController extends Controller
{
    use ClinicContext;

    public function index(Request $request): View
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $base = DentalLabOrder::where('clinic_id', $clinic->id);
        $ids = (clone $base)->pluck('id');

        $ordered = \App\Models\DentalLabOrderItem::whereIn('dental_lab_order_id', $ids)->sum(DB::raw('quantity * unit_price'));
        $labPaid = \App\Models\DentalLabPayment::whereIn('dental_lab_order_id', $ids)->sum('amount');
        $patientCharge = (clone $base)->whereNotNull('patient_price')->sum('patient_price');
        $patientPaid = \App\Models\DentalPatientPayment::whereIn('dental_lab_order_id', $ids)->sum('amount');

        $stats = [
            'count' => $ids->count(),
            'open' => (clone $base)->whereNotIn('status', [DentalLabOrderStatus::Received->value, DentalLabOrderStatus::Used->value])->count(),
            'owed_lab' => round(max($ordered - $labPaid, 0), 2),
            'due_patient' => round(max($patientCharge - $patientPaid, 0), 2),
        ];

        // Filters (kept in the query string so pagination carries them).
        $filters = [
            'q' => trim((string) $request->input('q')),
            'status' => (string) $request->input('status'),
            'dental_lab_id' => (string) $request->input('dental_lab_id'),
            'level' => (string) $request->input('level'), // patient | clinic
        ];

        $query = (clone $base)->with(['lab', 'patient', 'items', 'payments']);

        if (in_array($filters['status'], DentalLabOrderStatus::values(), true)) {
            $query->where('status', $filters['status']);
        }
        if ($filters['level'] === 'patient') {
            // Orders that include patient-related work (patient- or tooth-level).
            $query->whereHas('items', fn ($i) => $i->whereIn('level', [
                \App\Enums\DentalItemLevel::Patient->value,
                \App\Enums\DentalItemLevel::Tooth->value,
            ]));
        } elseif ($filters['level'] === 'clinic') {
            // Orders that include clinic materials.
            $query->whereHas('items', fn ($i) => $i->where('level', \App\Enums\DentalItemLevel::ClinicGeneral->value));
        }
        if ($filters['dental_lab_id'] !== '') {
            $query->where('dental_lab_id', $filters['dental_lab_id']);
        }
        if ($filters['q'] !== '') {
            $term = $filters['q'];
            $query->where(function ($w) use ($term) {
                if (ctype_digit($term)) {
                    $w->orWhere('id', (int) $term);
                }
                $w->orWhereHas('patient', fn ($p) => $p->where('name', 'like', "%{$term}%"));
                $w->orWhereHas('lab', fn ($l) => $l->where('name', 'like', "%{$term}%"));
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        $labs = DentalLab::forClinic($clinic->id)->orderBy('name')->get();

        return view('clinic.dental-lab.orders.index', compact('clinic', 'orders', 'stats', 'labs', 'filters'));
    }

    public function create(Request $request): View
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $data = $this->formData($clinic);
        // Preselect the patient when coming from the examination screen.
        $data['presetClientId'] = $request->integer('client_id') ?: null;

        return view('clinic.dental-lab.orders.create', $data);
    }

    public function store(DentalLabOrderRequest $request): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $this->assertLabInClinic($request->integer('dental_lab_id'), $clinic);

        $order = DB::transaction(function () use ($request, $clinic) {
            $order = DentalLabOrder::create([
                'clinic_id' => $clinic->id,
                'dental_lab_id' => $request->integer('dental_lab_id'),
                'client_id' => $request->integer('client_id') ?: null,
                'status' => DentalLabOrderStatus::Sent,
                'teeth_mode' => $request->input('teeth_mode', 'order'),
                'teeth' => $request->input('teeth_mode') === 'order'
                    ? DentalTooth::sanitize($request->input('teeth', []))
                    : null,
                'notes' => $request->input('notes'),
                'patient_price' => $request->filled('patient_price') ? $request->input('patient_price') : null,
                'operating_fee' => $request->filled('operating_fee') ? $request->input('operating_fee') : null,
                'created_by' => $request->user()->id,
            ]);

            $this->saveItems($order, $request->input('items', []));

            return $order;
        });

        return $this->afterSave($request, $order, __('app.dental.order.created'));
    }

    public function show(Request $request, DentalLabOrder $order): View
    {
        $clinic = $this->guardOrder($request, $order);
        $order->load(['lab', 'patient', 'items.item', 'payments.creator', 'patientPayments.creator', 'creator']);

        return view('clinic.dental-lab.orders.show', compact('clinic', 'order'));
    }

    public function edit(Request $request, DentalLabOrder $order): View|RedirectResponse
    {
        $clinic = $this->guardOrder($request, $order);

        // Once received or used, the order is tied to stock movements — editing
        // the lines then would desync the ledger, so it is locked. Explain it
        // rather than showing a bare 403. (Move the status back to edit.)
        if ($order->status->isFinal()) {
            return redirect()->route('practice.dental-lab.orders.show', $order)
                ->with('error', __('app.dental.order.locked'));
        }

        $order->load('items');

        return view('clinic.dental-lab.orders.edit', [
            'order' => $order,
            ...$this->formData($clinic),
        ]);
    }

    public function update(DentalLabOrderRequest $request, DentalLabOrder $order): RedirectResponse
    {
        $clinic = $this->guardOrder($request, $order);
        if ($order->status->isFinal()) {
            return redirect()->route('practice.dental-lab.orders.show', $order)
                ->with('error', __('app.dental.order.locked'));
        }
        $this->assertLabInClinic($request->integer('dental_lab_id'), $clinic);

        DB::transaction(function () use ($request, $order) {
            $order->update([
                'dental_lab_id' => $request->integer('dental_lab_id'),
                'client_id' => $request->integer('client_id') ?: null,
                'teeth_mode' => $request->input('teeth_mode', 'order'),
                'teeth' => $request->input('teeth_mode') === 'order'
                    ? DentalTooth::sanitize($request->input('teeth', []))
                    : null,
                'notes' => $request->input('notes'),
                'patient_price' => $request->filled('patient_price') ? $request->input('patient_price') : null,
                'operating_fee' => $request->filled('operating_fee') ? $request->input('operating_fee') : null,
            ]);

            // Simplest correct sync: replace the lines from the submitted set.
            $order->items()->delete();
            $this->saveItems($order, $request->input('items', []));
        });

        return $this->afterSave($request, $order, __('app.dental.order.updated'));
    }

    /**
     * Redirect to the order after create/update. When the form was shown in a
     * modal (embed), carry embed + a saved flag so the modal can auto-close.
     */
    private function afterSave(Request $request, DentalLabOrder $order, string $status): RedirectResponse
    {
        $params = $request->boolean('embed') ? [$order, 'embed' => 1, 'saved' => 1] : [$order];

        return redirect()->route('practice.dental-lab.orders.show', $params)->with('status', $status);
    }

    public function setStatus(Request $request, DentalLabOrder $order, DentalStockService $stock): RedirectResponse
    {
        $this->guardOrder($request, $order);

        $data = $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in(DentalLabOrderStatus::values())],
        ]);
        $new = DentalLabOrderStatus::from($data['status']);

        if ($new === $order->status) {
            return back();
        }

        DB::transaction(function () use ($order, $new, $stock) {
            $order->load('items');

            // Two independent stock layers, each driven by the target status:
            //   receipt     — materials sit in clinic stock   (received / used)
            //   consumption — materials used on the patient    (used)
            // "Used" is receipt + consumption, so its net stock effect is zero
            // but both movements are recorded, and it leaves the "received" list.
            $hadReceipt = $order->received_at !== null;
            $hadConsumed = $order->items->contains(fn ($i) => $i->used_at !== null);

            $wantReceipt = $new->holdsStock();
            $wantConsumed = $new->consumesStock();

            $order->status = $new;

            // 1. Bring materials into stock when first needed.
            if ($wantReceipt && ! $hadReceipt) {
                $order->received_at = now();
                $order->save();
                $order->load('items');
                $stock->applyOrderReceipt($order);
                $hadReceipt = true;
            }

            // 2. Consume onto the patient, or return to stock.
            if ($wantConsumed && ! $hadConsumed) {
                foreach ($order->items as $item) {
                    if ($item->tracksStock()) {
                        $stock->markItemUsed($item);
                    }
                }
            } elseif (! $wantConsumed && $hadConsumed) {
                foreach ($order->items as $item) {
                    if ($item->isUsed()) {
                        $stock->unmarkItemUsed($item);
                    }
                }
            }

            // 3. Drop out of the clinic entirely (back below received): reverse
            //    every movement this order made and clear the receipt stamp.
            if (! $wantReceipt && $hadReceipt) {
                $order->load('items');
                $stock->reverseOrderReceipt($order);
                $order->received_at = null;
            }

            $order->save();
        });

        return back()->with('status', __('app.dental.order.status_advanced', ['status' => $new->label()]));
    }

    public function destroy(Request $request, DentalLabOrder $order, DentalStockService $stock): RedirectResponse
    {
        $this->guardOrder($request, $order);

        DB::transaction(function () use ($order, $stock) {
            // Take back any materials this order added to stock before removing it.
            if ($order->received_at !== null) {
                $stock->reverseOrderReceipt($order);
            }
            $order->delete(); // items + payments cascade
        });

        return redirect()->route('practice.dental-lab.orders.index')
            ->with('status', __('app.dental.order.deleted'));
    }

    /**
     * Toggle a received material line between "in stock" and "used": marking it
     * used records a stock-out, un-marking returns it. Only material lines on a
     * received order can be used.
     */
    public function toggleItemUsed(Request $request, DentalLabOrder $order, DentalLabOrderItem $item, DentalStockService $stock): RedirectResponse
    {
        $this->guardOrder($request, $order);
        abort_unless($item->dental_lab_order_id === $order->id, 404);
        abort_unless($item->tracksStock(), 422);
        abort_unless($order->status === DentalLabOrderStatus::Received, 422);

        if ($item->isUsed()) {
            $stock->unmarkItemUsed($item);
            $message = __('app.dental.stock.item_returned');
        } else {
            $stock->markItemUsed($item);
            $message = __('app.dental.stock.item_used');
        }

        return back()->with('status', $message);
    }

    /**
     * A patient's current teeth marks (condition colour + treatment symbol),
     * keyed by Palmer code, so the order chart can show tooth states live when
     * the patient is changed.
     */
    public function patientMarks(Request $request, Client $client): \Illuminate\Http\JsonResponse
    {
        $this->activeClinic($this->clinicDoctor($request));

        return response()->json(\App\Models\ClinicalChartNote::teethMarks($client->id));
    }

    // ── helpers ──────────────────────────────────────────────────────────

    /** Shared data for the create/edit forms. */
    private function formData(Clinic $clinic): array
    {
        $labs = DentalLab::forClinic($clinic->id)->where('is_active', true)->orderBy('name')->get();

        $catalog = DentalItem::availableFor($clinic->id)->get();

        // Patients seen at this clinic, for the selector.
        $patientIds = \App\Models\Appointment::where('clinic_id', $clinic->id)
            ->distinct()->pluck('client_id')->filter();
        $patients = Client::whereIn('id', $patientIds)->orderBy('name')->get(['id', 'name', 'phone_number']);

        return [
            'clinic' => $clinic,
            'labs' => $labs,
            'catalog' => $catalog,
            'patients' => $patients,
            'arches' => DentalTooth::arches(),
        ];
    }

    /** Persist the submitted line items with snapshotted catalog fields. */
    private function saveItems(DentalLabOrder $order, array $items): void
    {
        $perItem = $order->teeth_mode === 'item';

        foreach ($items as $row) {
            if (trim((string) ($row['name'] ?? '')) === '') {
                continue;
            }

            $level = DentalItemLevel::tryFrom($row['level'] ?? '') ?? DentalItemLevel::Patient;

            $teeth = null;
            if ($perItem && $level === DentalItemLevel::Tooth) {
                $teeth = DentalTooth::sanitize($row['teeth'] ?? []);
            }

            $order->items()->create([
                'dental_item_id' => $row['dental_item_id'] ?? null,
                'level' => $level,
                'name' => $row['name'],
                'unit' => $row['unit'] ?? 'piece',
                'quantity' => $row['quantity'] ?? 1,
                'unit_price' => $row['unit_price'] ?? 0,
                'operating_fee' => $row['operating_fee'] ?? 0,
                'teeth' => $teeth,
            ]);
        }
    }

    private function assertLabInClinic(int $labId, Clinic $clinic): void
    {
        abort_unless(
            DentalLab::where('id', $labId)->where('clinic_id', $clinic->id)->exists(),
            422,
            __('app.dental.lab.not_in_clinic')
        );
    }

    private function guardOrder(Request $request, DentalLabOrder $order): Clinic
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($order->clinic_id === $clinic->id, 403);

        return $clinic;
    }
}
