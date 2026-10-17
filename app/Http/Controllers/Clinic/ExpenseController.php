<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\ExpenseStatus;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Expense;
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

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        ExpenseCategoryPresets::seedFor($clinic, $doctor);

        [$from, $to] = $this->period($request);
        $categoryId = $request->integer('category') ?: null;
        $status = in_array($request->query('status'), ExpenseStatus::values(), true)
            ? $request->query('status') : null;
        $search = trim((string) $request->query('search', ''));

        $expenses = Expense::forClinic($clinic->id)
            ->inPeriod($from->toDateString(), $to->toDateString())
            ->when($categoryId, fn ($q) => $q->where('expense_category_id', $categoryId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('title', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")))
            ->with(['category', 'payments', 'creator'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        // Totals cover the whole filtered set, not just the current page.
        $matching = Expense::forClinic($clinic->id)
            ->inPeriod($from->toDateString(), $to->toDateString())
            ->when($categoryId, fn ($q) => $q->where('expense_category_id', $categoryId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('title', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")))
            ->with('payments')
            ->get();

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
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categoryId' => $categoryId,
            'status' => $status,
            'search' => $search,
            'statuses' => ExpenseStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        ExpenseCategoryPresets::seedFor($clinic, $doctor);

        return view('clinic.expenses.create', [
            'clinic' => $clinic,
            'categories' => $this->categories($clinic),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        $data = $this->validateExpense($request, $clinic);

        $expense = $clinic->expenses()->create([
            'expense_category_id' => $data['expense_category_id'] ?? null,
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
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($request, $expense);
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $data = $this->validateExpense($request, $clinic);

        $expense->update([
            'expense_category_id' => $data['expense_category_id'] ?? null,
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
