<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Support\ClinicalChart;
use App\Support\ExpenseCategoryPresets;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manage the active clinic's expense categories (part of the Setup area).
 */
class ExpenseCategoryController extends Controller
{
    use ClinicContext;

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        ExpenseCategoryPresets::seedFor($clinic, $doctor);

        $categories = $clinic->expenseCategories()
            ->withCount('expenses')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $isDental = ClinicalChart::forSpecialization($doctor->specialization) === ClinicalChart::TEETH;

        return view('clinic.doctor.setup.expense-categories', compact('categories', 'clinic', 'isDental'));
    }

    /** Toggle the dental lab-payment auto-capture setting. */
    public function updateSettings(Request $request): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        $clinic->update(['expenses_autocapture' => $request->boolean('expenses_autocapture')]);

        return back()->with('status', __('app.expenses.settings_saved'));
    }

    public function store(Request $request): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:16'],
        ]);

        $clinic->expenseCategories()->create([
            'name' => $data['name'],
            'icon' => $data['icon'] ?? null,
            'is_active' => true,
            'sort_order' => (int) $clinic->expenseCategories()->max('sort_order') + 1,
        ]);

        return back()->with('status', __('app.expenses.category_saved'));
    }

    public function update(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $this->authorizeCategory($request, $category);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:16'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $data['name'],
            'icon' => $data['icon'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', __('app.expenses.category_updated'));
    }

    public function destroy(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $this->authorizeCategory($request, $category);
        // Keep historical expenses: they fall back to "uncategorised" (null FK).
        $category->delete();

        return back()->with('status', __('app.expenses.category_deleted'));
    }

    private function authorizeCategory(Request $request, ExpenseCategory $category): void
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($category->clinic_id === $clinic->id, 403);
    }
}
