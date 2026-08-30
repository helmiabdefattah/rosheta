<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicalChartNote;
use App\Support\ClinicalChart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Notes pinned to a body part during an examination.
 *
 * The chart is fixed by the acting doctor's specialisation — a dentist writes
 * on teeth — so it is never taken from the request. The region is validated
 * against that chart's own list, which means a forged one cannot be stored.
 */
class ClinicalChartNoteController extends Controller
{
    use ClinicContext;

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $chart = ClinicalChart::forSpecialization($doctor->specialization);
        abort_if($chart === null, 404);

        $rules = [
            'region' => ['required', Rule::in(array_keys(ClinicalChart::regions($chart)))],
            'note' => ['required', 'string', 'max:2000'],
        ];

        // Only point charts accept coordinates, and only inside the part's box.
        if (ClinicalChart::usesPoints($chart)) {
            $rules['point_x'] = ['nullable', 'numeric', 'between:0,1'];
            $rules['point_y'] = ['nullable', 'numeric', 'between:0,1'];
        }

        $data = $request->validate($rules);

        ClinicalChartNote::create([
            'client_id' => $appointment->client_id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'chart' => $chart,
            'region' => $data['region'],
            'point_x' => $data['point_x'] ?? null,
            'point_y' => $data['point_y'] ?? null,
            'note' => $data['note'],
        ]);

        return $this->backToRegion($appointment, $data['region'], __('app.chart.note_added'));
    }

    /**
     * Remove a note. A doctor may only take back their own — another doctor's
     * record of this patient is not theirs to erase.
     */
    public function destroy(Request $request, Appointment $appointment, ClinicalChartNote $note): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);
        abort_unless((int) $note->client_id === (int) $appointment->client_id, 404);
        abort_unless((int) $note->doctor_id === (int) $doctor->id, 403);

        $region = $note->region;
        $note->delete();

        return $this->backToRegion($appointment, $region, __('app.chart.note_removed'));
    }

    /**
     * Return to the examination with the chart open on the part just edited,
     * so the doctor is not hunting for the tooth they were working on.
     */
    private function backToRegion(Appointment $appointment, string $region, string $status): RedirectResponse
    {
        return redirect()
            ->to(route('practice.doctor.examine', $appointment).'?chart_region='.urlencode($region).'#clinical-chart')
            ->with('status', $status);
    }
}
