<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\MedicalPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Reusable prescription templates ("medical plans"): saved from the exam screen
 * or managed here, and loaded back into a prescription to speed up common Rx.
 */
class MedicalPlanController extends Controller
{
    use ClinicContext;

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $plans = $doctor->medicalPlans()->with('items')->orderBy('title')->get();

        return view('clinic.doctor.setup.medical-plans', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        $rows = $this->validatedItems($request);

        if ($rows->isEmpty()) {
            return back()->withErrors(['items' => __('app.plan.needs_item')])->withInput();
        }

        $plan = $doctor->medicalPlans()->create(['title' => $request->input('title')]);
        $rows->each(fn ($row) => $plan->items()->create($row));

        return back()->with('status', __('app.plan.saved', ['title' => $plan->title]));
    }

    public function edit(Request $request, MedicalPlan $medicalPlan): View
    {
        $this->authorizePlan($request, $medicalPlan);
        $medicalPlan->load('items');

        return view('clinic.doctor.setup.medical-plan-edit', ['plan' => $medicalPlan]);
    }

    public function update(Request $request, MedicalPlan $medicalPlan): RedirectResponse
    {
        $this->authorizePlan($request, $medicalPlan);
        $rows = $this->validatedItems($request);

        if ($rows->isEmpty()) {
            return back()->withErrors(['items' => __('app.plan.needs_item')])->withInput();
        }

        $medicalPlan->update(['title' => $request->input('title')]);
        $medicalPlan->items()->delete();
        $rows->each(fn ($row) => $medicalPlan->items()->create($row));

        return redirect()
            ->route('practice.doctor.setup.medical-plans')
            ->with('status', __('app.plan.updated', ['title' => $medicalPlan->title]));
    }

    public function destroy(Request $request, MedicalPlan $medicalPlan): RedirectResponse
    {
        $this->authorizePlan($request, $medicalPlan);
        $medicalPlan->delete();

        return back()->with('status', __('app.plan.deleted'));
    }

    /** Validate the title + medicine rows and return the non-empty rows. */
    private function validatedItems(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['nullable', 'string', 'max:255'],
            'items.*.dose' => ['nullable', 'string', 'max:255'],
            'items.*.frequency' => ['nullable', 'string', 'max:255'],
            'items.*.duration' => ['nullable', 'string', 'max:255'],
            'items.*.instructions' => ['nullable', 'string', 'max:255'],
        ]);

        return collect($data['items'])
            ->filter(fn ($i) => filled($i['medicine_name'] ?? null))
            ->map(fn ($i) => [
                'medicine_name' => $i['medicine_name'],
                'dose' => $i['dose'] ?? null,
                'frequency' => $i['frequency'] ?? null,
                'duration' => $i['duration'] ?? null,
                'instructions' => $i['instructions'] ?? null,
            ])
            ->values();
    }

    private function authorizePlan(Request $request, MedicalPlan $plan): void
    {
        abort_unless($plan->doctor_id === $this->clinicDoctor($request)->id, 403);
    }
}
