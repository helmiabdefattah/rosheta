<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\DentalLabRequest;
use App\Models\Clinic;
use App\Models\DentalLab;
use App\Models\DentalLabOrder;
use App\Models\DentalLabPayment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD for the dental laboratories a clinic works with. Everything is scoped to
 * the acting doctor's active clinic.
 */
class DentalLabController extends Controller
{
    use ClinicContext;
    use \App\Http\Controllers\Clinic\Concerns\DentalExports;

    public function index(Request $request): View
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $labs = DentalLab::forClinic($clinic->id)
            ->withCount('orders')
            ->orderBy('name')
            ->paginate(20);

        // Flat list for the export lab filter.
        $exportLabs = DentalLab::forClinic($clinic->id)->orderBy('name')->get(['id', 'name']);

        return view('clinic.dental-lab.labs.index', compact('clinic', 'labs', 'exportLabs'));
    }

    public function create(Request $request): View
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        return view('clinic.dental-lab.labs.create', compact('clinic'));
    }

    public function store(DentalLabRequest $request): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        DentalLab::create([
            'clinic_id' => $clinic->id,
            'created_by' => $request->user()->id,
            'is_active' => $request->boolean('is_active', true),
            ...$request->safe()->except('is_active'),
        ]);

        return redirect()->route('practice.dental-lab.labs.index')
            ->with('status', __('app.dental.lab.created'));
    }

    public function edit(Request $request, DentalLab $lab): View
    {
        $clinic = $this->guardLab($request, $lab);

        return view('clinic.dental-lab.labs.edit', compact('clinic', 'lab'));
    }

    public function update(DentalLabRequest $request, DentalLab $lab): RedirectResponse
    {
        $this->guardLab($request, $lab);

        $lab->update([
            'is_active' => $request->boolean('is_active', true),
            ...$request->safe()->except('is_active'),
        ]);

        return redirect()->route('practice.dental-lab.labs.index')
            ->with('status', __('app.dental.lab.updated'));
    }

    public function destroy(Request $request, DentalLab $lab): RedirectResponse
    {
        $this->guardLab($request, $lab);

        // Deleting a lab would cascade its orders — refuse while any exist, so
        // order and payment history is never lost by accident. Deactivate it
        // instead to hide it from new orders.
        if ($lab->orders()->exists()) {
            return back()->with('error', __('app.dental.lab.has_orders'));
        }

        $lab->delete();

        return redirect()->route('practice.dental-lab.labs.index')
            ->with('status', __('app.dental.lab.deleted'));
    }

    /**
     * Account statement for one lab: every order with its total and payments,
     * and the running balance between the clinic and the lab.
     */
    public function statement(Request $request, DentalLab $lab): View
    {
        $clinic = $this->guardLab($request, $lab);

        $orders = $lab->orders()
            ->with(['items', 'payments', 'patient'])
            ->latest()
            ->get();

        $totals = [
            'ordered' => round($orders->sum(fn (DentalLabOrder $o) => $o->total()), 2),
            'paid' => round($orders->sum(fn (DentalLabOrder $o) => $o->paidTotal()), 2),
        ];
        $totals['balance'] = round($totals['ordered'] - $totals['paid'], 2);

        return view('clinic.dental-lab.labs.statement', compact('clinic', 'lab', 'orders', 'totals'));
    }

    /**
     * Export the clinic's lab orders over a date range as PDF or Excel (CSV).
     * ?format=pdf|excel, ?from=Y-m-d, ?to=Y-m-d. With no dates, the current month.
     */
    public function exportOrders(Request $request)
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        [$from, $to, $periodLabel] = $this->exportPeriod($request);
        $labId = $this->exportLabId($request, $clinic);
        if ($labId) {
            $periodLabel = (DentalLab::find($labId)?->name ?? '').' · '.$periodLabel;
        }

        $orders = DentalLabOrder::where('clinic_id', $clinic->id)
            ->when($labId, fn ($q) => $q->where('dental_lab_id', $labId))
            ->whereBetween('created_at', [$from, $to])
            ->with(['lab', 'items', 'payments', 'patientPayments'])
            ->latest()
            ->get();

        $columns = [
            '#',
            __('app.dental.order.sent'),
            __('app.dental.order.lab'),
            __('app.dental.order.patient'),
            __('app.dental.order.status'),
            __('app.dental.line.title'),
            __('app.dental.patient_account.lab_price'),
            __('app.dental.patient_account.lab_paid'),
            __('app.dental.patient_account.lab_due'),
            __('app.dental.patient_account.charge'),
            __('app.dental.patient_account.paid'),
            __('app.dental.patient_account.clinic_due'),
        ];

        $rows = $orders->map(fn (DentalLabOrder $o) => [
            $o->id,
            $o->created_at?->format('Y-m-d'),
            $o->lab?->name ?? '—',
            $o->patient?->name ?? __('app.dental.order.no_patient'),
            $o->status->label(),
            $this->itemsSummary($o),
            number_format($o->total(), 2),
            number_format($o->paidTotal(), 2),
            number_format($o->balance(), 2),
            number_format($o->patientCharge(), 2),
            number_format($o->patientPaidTotal(), 2),
            number_format($o->patientBalance(), 2),
        ])->all();

        $totals = [
            __('app.dental.order.title'), '', '', '', '', '',
            number_format($orders->sum(fn (DentalLabOrder $o) => $o->total()), 2),
            number_format($orders->sum(fn (DentalLabOrder $o) => $o->paidTotal()), 2),
            number_format($orders->sum(fn (DentalLabOrder $o) => $o->balance()), 2),
            number_format($orders->sum(fn (DentalLabOrder $o) => $o->patientCharge()), 2),
            number_format($orders->sum(fn (DentalLabOrder $o) => $o->patientPaidTotal()), 2),
            number_format($orders->sum(fn (DentalLabOrder $o) => $o->patientBalance()), 2),
        ];

        return $this->exportResponse(
            $request, $clinic, 'orders',
            __('app.dental.export.orders_title'), $periodLabel, $columns, $rows, $totals
        );
    }

    /** Export the clinic's lab payments over a date range as PDF or Excel (CSV). */
    public function exportPayments(Request $request)
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        [$from, $to, $periodLabel] = $this->exportPeriod($request);
        $labId = $this->exportLabId($request, $clinic);
        if ($labId) {
            $periodLabel = (DentalLab::find($labId)?->name ?? '').' · '.$periodLabel;
        }

        $payments = DentalLabPayment::whereHas('order', fn ($q) => $q->where('clinic_id', $clinic->id)
                ->when($labId, fn ($q) => $q->where('dental_lab_id', $labId)))
            ->whereBetween('paid_at', [$from, $to])
            ->with('order.lab')
            ->latest('paid_at')
            ->get();

        $columns = [
            __('app.dental.payment.date'),
            '#',
            __('app.dental.order.lab'),
            __('app.dental.payment.amount'),
            __('app.dental.payment.method'),
            __('app.dental.payment.note'),
        ];

        $rows = $payments->map(fn (DentalLabPayment $p) => [
            $p->paid_at?->format('Y-m-d'),
            $p->dental_lab_order_id,
            $p->order?->lab?->name ?? '—',
            number_format((float) $p->amount, 2),
            $p->method ?: '—',
            $p->note ?: '—',
        ])->all();

        $totals = [
            __('app.dental.payment.title'), '', '',
            number_format($payments->sum('amount'), 2), '', '',
        ];

        return $this->exportResponse(
            $request, $clinic, 'payments',
            __('app.dental.export.payments_title'), $periodLabel, $columns, $rows, $totals
        );
    }

    /**
     * The selected lab to scope an export to, or null for all labs. Validated
     * against the clinic so a forged id cannot read another clinic's lab.
     */
    private function exportLabId(Request $request, Clinic $clinic): ?int
    {
        $id = $request->integer('dental_lab_id');
        if (! $id) {
            return null;
        }

        return DentalLab::forClinic($clinic->id)->whereKey($id)->exists() ? $id : null;
    }

    /**
     * One-cell summary of an order's line items for the export:
     * "Crown ×2 (UR7, DR1) · Bite registration ×1".
     */
    private function itemsSummary(DentalLabOrder $order): string
    {
        return $order->items->map(function (\App\Models\DentalLabOrderItem $it) {
            $qty = rtrim(rtrim(number_format((float) $it->quantity, 2), '0'), '.');
            $line = $it->name.' ×'.$qty;
            $teeth = $it->effectiveTeeth();
            if ($teeth) {
                $line .= ' ('.implode(', ', $teeth).')';
            }

            return $line;
        })->implode(' · ');
    }

    /** Resolve the clinic and ensure the lab belongs to it. */
    private function guardLab(Request $request, DentalLab $lab): Clinic
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($lab->clinic_id === $clinic->id, 403);

        return $clinic;
    }
}
