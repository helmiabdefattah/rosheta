<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\RecurrenceInterval;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\RecurringExpense;
use App\Support\ExpenseCategoryPresets;
use App\Support\RecurringExpenseGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Recurring-expense templates: rent, salaries, subscriptions and the like. The
 * scheduled command (and the "generate now" button here) turn due templates
 * into real expenses.
 */
class RecurringExpenseController extends Controller
{
    use ClinicContext;

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        ExpenseCategoryPresets::seedFor($clinic, $doctor);

        $templates = $clinic->recurringExpenses()
            ->with(['category', 'vendor'])
            ->orderBy('next_run_on')
            ->get();

        return view('clinic.recurring.index', [
            'clinic' => $clinic,
            'templates' => $templates,
            'categories' => $this->categories($clinic),
            'vendors' => $this->vendors($clinic),
            'intervals' => RecurrenceInterval::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $data = $this->validateTemplate($request, $clinic);

        $clinic->recurringExpenses()->create($data + ['is_active' => true]);

        return back()->with('status', __('app.recurring.saved'));
    }

    public function edit(Request $request, RecurringExpense $recurring): View
    {
        $this->authorizeTemplate($request, $recurring);
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        return view('clinic.recurring.edit', [
            'template' => $recurring,
            'categories' => $this->categories($clinic),
            'vendors' => $this->vendors($clinic),
            'intervals' => RecurrenceInterval::cases(),
        ]);
    }

    public function update(Request $request, RecurringExpense $recurring): RedirectResponse
    {
        $this->authorizeTemplate($request, $recurring);
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $data = $this->validateTemplate($request, $clinic);

        $recurring->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('app.recurring.updated'));
    }

    public function destroy(Request $request, RecurringExpense $recurring): RedirectResponse
    {
        $this->authorizeTemplate($request, $recurring);
        $recurring->delete();

        return back()->with('status', __('app.recurring.deleted'));
    }

    /** Run the generator for this clinic now. */
    public function generate(Request $request, RecurringExpenseGenerator $generator): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $count = $generator->generateDue($clinic->id);

        return back()->with('status', __('app.recurring.generated', ['count' => $count]));
    }

    private function validateTemplate(Request $request, Clinic $clinic): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'tax_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'interval' => ['required', Rule::in(RecurrenceInterval::values())],
            'next_run_on' => ['required', 'date'],
            'expense_category_id' => [
                'nullable',
                Rule::exists('expense_categories', 'id')->where('clinic_id', $clinic->id),
            ],
            'vendor_id' => [
                'nullable',
                Rule::exists('vendors', 'id')->where('clinic_id', $clinic->id),
            ],
        ]);
    }

    private function categories(Clinic $clinic)
    {
        return $clinic->expenseCategories()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    private function vendors(Clinic $clinic)
    {
        return $clinic->vendors()->where('is_active', true)->orderBy('name')->get();
    }

    private function authorizeTemplate(Request $request, RecurringExpense $template): void
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($template->clinic_id === $clinic->id, 403);
    }
}
