<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\VendorType;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Clinic\Concerns\TableExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Clinic vendors/suppliers: directory with running balances, CRUD, and a
 * per-vendor account statement (its expenses, what was paid, what is owed).
 */
class VendorController extends Controller
{
    use ClinicContext;
    use TableExport;

    public function index(Request $request): View
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $vendors = $clinic->vendors()
            ->with(['expenses.payments'])
            ->orderBy('name')
            ->get();

        $totals = [
            'billed' => round($vendors->sum(fn (Vendor $v) => $v->totalBilled()), 2),
            'paid' => round($vendors->sum(fn (Vendor $v) => $v->totalPaid()), 2),
            'balance' => round($vendors->sum(fn (Vendor $v) => $v->balance()), 2),
        ];

        return view('clinic.vendors.index', [
            'clinic' => $clinic,
            'vendors' => $vendors,
            'totals' => $totals,
            'types' => VendorType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $data = $this->validateVendor($request);

        $clinic->vendors()->create($data + ['is_active' => true]);

        return back()->with('status', __('app.vendors.saved'));
    }

    public function edit(Request $request, Vendor $vendor): View
    {
        $this->authorizeVendor($request, $vendor);

        return view('clinic.vendors.edit', [
            'vendor' => $vendor,
            'types' => VendorType::cases(),
        ]);
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorizeVendor($request, $vendor);
        $data = $this->validateVendor($request);

        $vendor->update($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()
            ->route('practice.doctor.vendors.index')
            ->with('status', __('app.vendors.updated'));
    }

    public function destroy(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorizeVendor($request, $vendor);
        // Expenses stay; they just lose the vendor link (nullOnDelete).
        $vendor->delete();

        return back()->with('status', __('app.vendors.deleted'));
    }

    public function statement(Request $request, Vendor $vendor): View
    {
        $this->authorizeVendor($request, $vendor);
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $expenses = $vendor->expenses()->with('payments')->orderByDesc('expense_date')->get();

        return view('clinic.vendors.statement', [
            'clinic' => $clinic,
            'vendor' => $vendor,
            'expenses' => $expenses,
        ]);
    }

    /** PDF / CSV of a vendor's statement. */
    public function exportStatement(Request $request, Vendor $vendor)
    {
        $this->authorizeVendor($request, $vendor);
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $expenses = $vendor->expenses()->with('payments')->orderByDesc('expense_date')->get();

        $columns = [
            __('app.expenses.f.date'),
            __('app.expenses.f.title'),
            __('app.expenses.total'),
            __('app.expenses.paid'),
            __('app.expenses.outstanding'),
            __('app.expenses.f.status'),
        ];

        $rows = $expenses->map(fn (Expense $e) => [
            $e->expense_date?->format('Y-m-d'),
            $e->title,
            number_format($e->gross(), 2),
            number_format($e->paidTotal(), 2),
            number_format($e->balance(), 2),
            $e->status->label(),
        ])->all();

        $totals = [
            __('app.vendors.opening').' '.number_format((float) $vendor->opening_balance, 2),
            '',
            number_format($vendor->totalBilled(), 2),
            number_format($vendor->totalPaid(), 2),
            number_format($vendor->balance(), 2),
            '',
        ];

        return $this->tableExportResponse(
            $request, $clinic, 'vendor-'.$vendor->id.'-statement',
            __('app.vendors.statement').' · '.$vendor->name, now()->format('Y-m-d'),
            $columns, $rows, $totals
        );
    }

    private function validateVendor(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(VendorType::values())],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function authorizeVendor(Request $request, Vendor $vendor): void
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($vendor->clinic_id === $clinic->id, 403);
    }
}
