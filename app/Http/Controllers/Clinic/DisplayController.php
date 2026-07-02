<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Waiting-room display / screen-saver for a single clinic (public). Polls for
 * the patient currently under examination and announces them by voice.
 */
class DisplayController extends Controller
{
    public function screen(Clinic $clinic): View
    {
        return view('clinic.display.screen', compact('clinic'));
    }

    /** JSON snapshot of the patient currently under examination today. */
    public function current(Clinic $clinic): JsonResponse
    {
        $appointment = Appointment::where('doctor_id', $clinic->doctor_id)
            ->today()
            ->where('status', 'under_examination')
            ->latest('updated_at')
            ->first();

        return response()->json([
            'current' => $appointment ? [
                'id' => $appointment->id,
                'queue_number' => $appointment->queue_number,
                'sort_number' => $this->sortNumber($appointment),
            ] : null,
        ]);
    }

    /**
     * Advance the queue from the display: complete whoever is being examined,
     * then promote the next scheduled patient (lowest queue number) forward.
     */
    public function next(Clinic $clinic): JsonResponse
    {
        $doctorId = $clinic->doctor_id;

        $next = DB::transaction(function () use ($doctorId) {
            $current = Appointment::where('doctor_id', $doctorId)->today()
                ->where('status', 'under_examination')
                ->orderByDesc('queue_number')
                ->first();
            $currentQueue = $current?->queue_number;

            Appointment::where('doctor_id', $doctorId)->today()
                ->where('status', 'under_examination')
                ->get()
                ->each(fn (Appointment $appt) => $appt->update(['status' => 'completed']));

            $query = Appointment::where('doctor_id', $doctorId)->today()->where('status', 'scheduled');
            if ($currentQueue !== null) {
                $query->where('queue_number', '>', $currentQueue);
            }
            $next = (clone $query)->orderBy('queue_number')->orderBy('scheduled_at')->first();

            if (! $next) {
                $fallback = Appointment::where('doctor_id', $doctorId)->today()->where('status', 'scheduled');
                if ($currentQueue !== null) {
                    $fallback->where('queue_number', '!=', $currentQueue);
                }
                $next = $fallback->orderBy('queue_number')->orderBy('scheduled_at')->first();
            }

            if ($next) {
                $next->update(['status' => 'under_examination']);
            }

            return $next;
        });

        return response()->json([
            'current' => $next ? [
                'id' => $next->id,
                'queue_number' => $next->queue_number,
                'sort_number' => $this->sortNumber($next),
            ] : null,
        ]);
    }

    /**
     * 1-based position of an appointment among the doctor's appointments today,
     * ordered the same way the dashboards list them (queue number, then time).
     * Matches the "now serving" sort number shown on the assistant dashboard.
     */
    protected function sortNumber(Appointment $appointment): int
    {
        $position = Appointment::where('doctor_id', $appointment->doctor_id)
            ->today()
            ->orderBy('queue_number')
            ->orderBy('scheduled_at')
            ->pluck('id')
            ->search($appointment->id);

        return $position === false ? $appointment->queue_number : $position + 1;
    }
}
