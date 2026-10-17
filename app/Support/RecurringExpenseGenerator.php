<?php

namespace App\Support;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\RecurringExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Turns due recurring-expense templates into real expenses. For each template
 * whose next_run_on has fallen due it creates one expense per missed period
 * (catching up), then rolls next_run_on forward. Safe to run repeatedly — only
 * periods not yet generated produce rows.
 */
class RecurringExpenseGenerator
{
    /** Guard against a mis-set template generating forever. */
    private const MAX_CATCHUP = 120;

    /**
     * Generate due expenses, optionally scoped to one clinic. Returns how many
     * expenses were created.
     */
    public function generateDue(?int $clinicId = null, ?Carbon $onDate = null): int
    {
        $today = ($onDate ?? now())->copy()->startOfDay();

        $templates = RecurringExpense::due($today->toDateString())
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->get();

        $created = 0;
        foreach ($templates as $template) {
            $created += $this->generateOne($template, $today);
        }

        return $created;
    }

    /** Generate every due period for one template and roll it forward. */
    public function generateOne(RecurringExpense $template, Carbon $today): int
    {
        if (! $template->is_active) {
            return 0;
        }

        return DB::transaction(function () use ($template, $today) {
            $created = 0;
            $next = $template->next_run_on->copy();

            while ($next->lte($today) && $created < self::MAX_CATCHUP) {
                $expense = new Expense([
                    'clinic_id' => $template->clinic_id,
                    'expense_category_id' => $template->expense_category_id,
                    'vendor_id' => $template->vendor_id,
                    'title' => $template->title,
                    'amount' => $template->amount,
                    'tax_amount' => $template->tax_amount,
                    'expense_date' => $next->toDateString(),
                    'status' => ExpenseStatus::Unpaid,
                ]);
                $expense->source()->associate($template);
                $expense->save();

                $template->last_generated_on = $next->copy();
                $next = $template->interval->advance($next);
                $created++;
            }

            if ($created > 0) {
                $template->next_run_on = $next;
                $template->save();
            }

            return $created;
        });
    }
}
