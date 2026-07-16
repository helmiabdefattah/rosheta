<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The staff-facing "assistant screen": the same big queue counter the
 * waiting-room display shows, plus today's patient list, a check-in shortcut
 * and the call-next button.
 *
 * Unlike the public display (which shows a bare number so no patient data
 * leaks to the waiting room), this screen lists patient names — so it lives
 * behind auth + clinic.role and resolves its clinic from the signed-in user
 * rather than a URL parameter.
 */
class AssistantScreenController extends Controller
{
    use ClinicContext;

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);

        return view('clinic.display.assistant', [
            'doctor' => $doctor,
            'clinic' => $this->activeClinic($doctor),
        ]);
    }

    /** JSON snapshot: who's under examination + today's queue, in list order. */
    public function queue(Request $request): JsonResponse
    {
        $doctor = $this->clinicDoctor($request);

        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->today()
            ->with('client')
            ->orderBy('queue_number')
            ->orderBy('scheduled_at')
            ->get();

        $current = $appointments->firstWhere('status', 'under_examination');

        return response()->json([
            'current' => $current ? [
                'id' => $current->id,
                // Position in the list, matching the dashboard's "now serving"
                // number and the public display's sort_number.
                'sort_number' => $appointments->search(fn ($a) => $a->id === $current->id) + 1,
                'name' => $current->client?->name,
            ] : null,
            'queue' => $appointments->values()->map(fn (Appointment $a, int $i) => [
                'id' => $a->id,
                'sort_number' => $i + 1,
                'name' => $a->client?->name,
                'time' => $a->scheduled_at?->format('H:i'),
                'status' => $a->status,
                'status_label' => $a->statusLabel(),
            ]),
        ]);
    }
}
