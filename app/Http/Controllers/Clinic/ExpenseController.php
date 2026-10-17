<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\ExpenseStatus;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Clinic\Concerns\TableExport;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Expense;
use App\Models\Vendor;
use App\Support\ExpenseCategoryPresets;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Clinic expenses: list with period/category/status filters and totals, plus
 * create / edit / delete. Everything is scoped to the doctor's active clinic.
 */
class ExpenseController extends Controller
{
    use ClinicContext;
    use TableExport;

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        ExpenseCategoryPresets::seedFor($clinic, $doctor);

        [$from, $to] = $this->period($request);
        $filters = $this->filters($request);

        $expenses = $this->filtered($clinic, $from, $to, $filters)
            ->with(['category', 'vendor', 'payments', 'creator'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        // Totals cover the whole filtered set, not just the current page.
        $matching = $this->filtered($clinic, $from, $to, $filters)->with('payments')->get();

        $totals = [
            'gross' => round($matching->sum(fn (Expense $e) => $e->gross()), 2),
            'paid' => round($matching->sum(fn (Expense $e) => $e->paidTotal()), 2),
            'outstanding' => round($matching->sum(fn (Expense $e) => $e->balance()), 2),
            'count' => $matching->count(),
        ];

        return view('clinic.expenses.index', [
            'clinic' => $clinic,
            'expenses' => $expenses,
            'totals' => $totals,
            'categories' => $this->categories($clinic),
            'vendors' => $this->vendors($clinic),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categoryId' => $filters['category'],
            'vendorId' => $filters['vendor'],
            'status' => $filters['status'],
            'search' => $filters['search'],
            'statuses' => ExpenseStatus::cases(),
        ]);
    }

    /** PDF / CSV of the filtered expenses list. */
    public function export(Request $request)
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        [$from, $to] = $this->period($request);
        $filters = $this->filters($request);

        $expenses = $this->filtered($clinic, $from, $to, $filters)
            ->with(['category', 'vendor', 'payments'])
            ->orderByDesc('expense_date')
            ->get();

        $columns = [
            __('app.expenses.f.date'),
            __('app.expenses.f.title'),
            __('app.expenses.f.category'),
            __('app.expenses.f.vendor'),
            __('app.expenses.total'),
            __('app.expenses.paid'),
            __('app.expenses.outstanding'),
            __('app.expenses.f.status'),
        ];

        $rows = $expenses->map(fn (Expense $e) => [
            $e->expense_date?->format('Y-m-d'),
            $e->title,
            $e->category?->name ?? '—',
            $e->vendor?->name ?? '—',
            number_format($e->gross(), 2),
            number_format($e->paidTotal(), 2),
            number_format($e->balance(), 2),
            $e->status->label(),
        ])->all();

        $totals = [
            __('app.expenses.total'), '', '', '',
            number_format($expenses->sum(fn (Expense $e) => $e->gross()), 2),
            number_format($expenses->sum(fn (Expense $e) => $e->paidTotal()), 2),
            number_format($expenses->sum(fn (Expense $e) => $e->balance()), 2),
            '',
        ];

        return $this->tableExportResponse(
            $request, $clinic, 'expenses',
            __('app.expenses.title'), $from->format('Y-m-d').' — '.$to->format('Y-m-d'),
            $columns, $rows, $totals
        );
    }

    public function create(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        ExpenseCategoryPresets::seedFor($clinic, $doctor);

        return view('clinic.expenses.create', [
            'clinic' => $clinic,
            'categories' => $this->categories($clinic),
            'vendors' => $this->vendors($clinic),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        $data = $this->validateExpense($request, $clinic);

        $expense = $clinic->expenses()->create([
            'expense_category_id' => $data['expense_category_id'] ?? null,
            'vendor_id' => $data['vendor_id'] ?? null,
            'created_by' => $request->user()?->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'tax_amount' => $data['tax_amount'] ?? null,
            'expense_date' => $data['expense_date'],
            'reference_no' => $data['reference_no'] ?? null,
            'status' => ExpenseStatus::Unpaid,
        ]);

        $this->attachReceipts($request, $expense);
        $expense->recomputeStatus();

        return redirect()
            ->route('practice.doctor.expenses.show', $expense)
            ->with('status', __('app.expenses.saved'));
    }

    public function show(Request $request, Expense $expense): View
    {
        $this->authorizeExpense($request, $expense);
        $expense->load(['category', 'payments.payer', 'creator', 'media']);

        return view('clinic.expenses.show', ['expense' => $expense]);
    }

    public function edit(Request $request, Expense $expense): View
    {
        $this->authorizeExpense($request, $expense);
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        return view('clinic.expenses.edit', [
            'expense' => $expense,
            'clinic' => $clinic,
            'categories' => $this->categories($clinic),
            'vendors' => $this->vendors($clinic),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($request, $expense);
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $data = $this->validateExpense($request, $clinic);

        $expense->update([
            'expense_category_id' => $data['expense_category_id'] ?? null,
            'vendor_id' => $data['vendor_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'tax_amount' => $data['tax_amount'] ?? null,
            'expense_date' => $data['expense_date'],
            'reference_no' => $data['reference_no'] ?? null,
        ]);

        $this->attachReceipts($request, $expense);
        $expense->recomputeStatus();

        return redirect()
            ->route('practice.doctor.expenses.show', $expense)
            ->with('status', __('app.expenses.updated'));
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($request, $expense);
        $expense->delete();

        return redirect()
            ->route('practice.doctor.expenses.index')
            ->with('status', __('app.expenses.deleted'));
    }

    /** Validate the expense form; the category must belong to this clinic. */
    private function validateExpense(Request $request, Clinic $clinic): array
    {
        return $request->validate([
            'expense_category_id' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('expense_categories', 'id')->where('clinic_id', $clinic->id),
            ],
            'vendor_id' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('vendors', 'id')->where('clinic_id', $clinic->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'tax_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'expense_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'receipts' => ['nullable', 'array'],
            'receipts.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ]);
    }

    private function attachReceipts(Request $request, Expense $expense): void
    {
        foreach ((array) $request->file('receipts', []) as $file) {
            if ($file && $file->isValid()) {
                $expense->addMedia($file)->toMediaCollection('receipts');
            }
        }
    }

    /** Active categories for this clinic, in display order. */
    private function categories(Clinic $clinic)
    {
        return $clinic->expenseCategories()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /** Active vendors for this clinic, by name. */
    private function vendors(Clinic $clinic)
    {
        return $clinic->vendors()->where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Normalise the list filters from the request.
     *
     * @return array{category: ?int, vendor: ?int, status: ?string, search: string}
     */
    private function filters(Request $request): array
    {
        return [
            'category' => $request->integer('category') ?: null,
            'vendor' => $request->integer('vendor') ?: null,
            'status' => in_array($request->query('status'), ExpenseStatus::values(), true)
                ? $request->query('status') : null,
            'search' => trim((string) $request->query('search', '')),
        ];
    }

    /** The filtered expense query shared by the list, totals and export. */
    private function filtered(Clinic $clinic, Carbon $from, Carbon $to, array $filters)
    {
        return Expense::forClinic($clinic->id)
            ->inPeriod($from->toDateString(), $to->toDateString())
            ->when($filters['category'], fn ($q) => $q->where('expense_category_id', $filters['category']))
            ->when($filters['vendor'], fn ($q) => $q->where('vendor_id', $filters['vendor']))
            ->when($filters['status'], fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['search'] !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('title', 'like', "%{$filters['search']}%")
                ->orWhere('reference_no', 'like', "%{$filters['search']}%")));
    }

    /** 403 unless the expense belongs to the doctor's active clinic. */
    private function authorizeExpense(Request $request, Expense $expense): void
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($expense->clinic_id === $clinic->id, 403);
    }

    /** Parse ?from=&to= (Y-m-d); default to the current month. */
    private function period(Request $request): array
    {
        $from = $this->parseDate($request->query('from')) ?? Carbon::now()->startOfMonth();
        $to = $this->parseDate($request->query('to')) ?? Carbon::now()->endOfMonth();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->startOfDay()];
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! is_string($value) || ! Carbon::hasFormat($value, 'Y-m-d')) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $value);
    }
}
