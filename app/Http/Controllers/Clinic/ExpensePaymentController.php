<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\ExpensePaymentMethod;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpensePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Payments recorded against an expense. Each add/remove recomputes the parent
 * expense's status from the full payments ledger.
 */
class ExpensePaymentController extends Controller
{
    use ClinicContext;

    public function store(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($request, $expense);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'paid_at' => ['required', 'date'],
            'method' => ['nullable', Rule::in(ExpensePaymentMethod::values())],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $expense->payments()->create([
            'amount' => $data['amount'],
            'paid_at' => $data['paid_at'],
            'method' => $data['method'] ?? null,
            'paid_by' => $request->user()?->id,
            'note' => $data['note'] ?? null,
        ]);

        $expense->load('payments')->recomputeStatus();

        return back()->with('status', __('app.expenses.payment_added'));
    }

    public function destroy(Request $request, ExpensePayment $payment): RedirectResponse
    {
        $expense = $payment->expense;
        $this->authorizeExpense($request, $expense);

        $payment->delete();
        $expense->load('payments')->recomputeStatus();

        return back()->with('status', __('app.expenses.payment_deleted'));
    }

    /** 403 unless the expense belongs to the doctor's active clinic. */
    private function authorizeExpense(Request $request, Expense $expense): void
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($expense->clinic_id === $clinic->id, 403);
    }
}
