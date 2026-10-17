<?php

namespace App\Http\Controllers\Clinic;

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
 * Monthly budgets per category, shown against what was actually spent that
 * month (budget vs. actual with variance).
 */
class ExpenseBudgetController extends Controller
{
    use ClinicContext;

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        ExpenseCategoryPresets::seedFor($clinic, $doctor);

        $month = $this->month($request);
        $from = $month->toDateString();
        $to = $month->copy()->endOfMonth()->toDateString();

        $categories = $clinic->expenseCategories()->where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')->get();

        // Budgets for this month, keyed by category id (0 = overall).
        $budgets = $clinic->expenseBudgets()
            ->whereDate('period_month', $from)
            ->get()
            ->keyBy(fn ($b) => $b->expense_category_id ?? 0);

        // Actual spend this month per category, plus the overall total.
        $spentByCategory = Expense::forClinic($clinic->id)
            ->inPeriod($from, $to)
            ->with('payments')
            ->get()
            ->groupBy(fn (Expense $e) => $e->expense_category_id ?? 0)
            ->map(fn ($group) => round($group->sum(fn (Expense $e) => $e->gross()), 2));

        $rows = $categories->map(function ($cat) use ($budgets, $spentByCategory) {
            $budget = (float) ($budgets[$cat->id]->amount ?? 0);
            $actual = (float) ($spentByCategory[$cat->id] ?? 0);

            return [
                'category' => $cat,
                'budget' => $budget,
                'actual' => $actual,
                'variance' => round($budget - $actual, 2),
                'pct' => $budget > 0 ? min(100, round($actual / $budget * 100)) : null,
            ];
        });

        $overall = [
            'budget' => (float) ($budgets[0]->amount ?? 0),
            'actual' => round($spentByCategory->sum(), 2),
        ];
        $overall['variance'] = round($overall['budget'] - $overall['actual'], 2);
        $overall['pct'] = $overall['budget'] > 0 ? min(100, round($overall['actual'] / $overall['budget'] * 100)) : null;

        return view('clinic.budgets.index', [
            'clinic' => $clinic,
            'rows' => $rows,
            'overall' => $overall,
            'month' => $month->toDateString(),
            'monthLabel' => $month->translatedFormat('F Y'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $data = $request->validate([
            'month' => ['required', 'date'],
            'overall' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'budgets' => ['nullable', 'array'],
            'budgets.*' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);

        $month = Carbon::parse($data['month'])->startOfMonth()->toDateString();

        $this->upsertBudget($clinic, null, $month, $data['overall'] ?? null);

        foreach ($data['budgets'] ?? [] as $categoryId => $amount) {
            // Only accept categories that belong to this clinic.
            if (! $clinic->expenseCategories()->whereKey((int) $categoryId)->exists()) {
                continue;
            }
            $this->upsertBudget($clinic, (int) $categoryId, $month, $amount);
        }

        return redirect()
            ->route('practice.doctor.budgets.index', ['month' => $month])
            ->with('status', __('app.budgets.saved'));
    }

    /** Set, update, or clear (on blank/zero) one budget row. */
    private function upsertBudget(Clinic $clinic, ?int $categoryId, string $month, $amount): void
    {
        $query = $clinic->expenseBudgets()
            ->where('expense_category_id', $categoryId)
            ->whereDate('period_month', $month);

        if ($amount === null || $amount === '' || (float) $amount <= 0) {
            $query->delete();

            return;
        }

        $existing = $query->first();
        if ($existing) {
            $existing->update(['amount' => $amount]);
        } else {
            $clinic->expenseBudgets()->create([
                'expense_category_id' => $categoryId,
                'period_month' => $month,
                'amount' => $amount,
            ]);
        }
    }

    private function month(Request $request): Carbon
    {
        $value = $request->query('month');
        $date = is_string($value) && strtotime($value) ? Carbon::parse($value) : Carbon::now();

        return $date->startOfMonth();
    }
}
