<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\ExaminationField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Doctor-defined custom examination fields (text / select / number /
 * percentage / file) shown on the examination screen.
 */
class ExaminationFieldController extends Controller
{
    use ClinicContext;

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $fields = $doctor->examinationFields()->orderBy('sort_order')->orderBy('id')->get();

        return view('clinic.doctor.setup.examination-fields', compact('fields'));
    }

    public function store(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        $data = $this->validated($request);

        $doctor->examinationFields()->create([
            'label' => $data['label'],
            'type' => $data['type'],
            'options' => $data['options'] ?? null,
            'is_active' => true,
            'sort_order' => (int) $doctor->examinationFields()->max('sort_order') + 1,
        ]);

        return back()->with('status', __('app.field.added'));
    }

    public function update(Request $request, ExaminationField $examinationField): RedirectResponse
    {
        $this->authorizeField($request, $examinationField);
        $data = $this->validated($request);

        $examinationField->update([
            'label' => $data['label'],
            'type' => $data['type'],
            'options' => $data['options'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('practice.doctor.setup.examination-fields')
            ->with('status', __('app.field.updated'));
    }

    public function destroy(Request $request, ExaminationField $examinationField): RedirectResponse
    {
        $this->authorizeField($request, $examinationField);
        $examinationField->delete();

        return back()->with('status', __('app.field.deleted'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(ExaminationField::TYPES)],
            'options' => ['nullable', 'string', 'max:1000'],
        ]);

        // "select" needs at least one comma-separated choice.
        if ($data['type'] === 'select' && blank($data['options'] ?? null)) {
            throw ValidationException::withMessages([
                'options' => __('app.field.options_required'),
            ]);
        }

        return $data;
    }

    private function authorizeField(Request $request, ExaminationField $field): void
    {
        abort_unless($field->doctor_id === $this->clinicDoctor($request)->id, 403);
    }
}
