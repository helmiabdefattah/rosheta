<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\BuildsClinicCalendar;
use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Notifications\PrintQueueTicketNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AssistantDashboardController extends Controller
{
    use BuildsClinicCalendar, ClinicContext;

    /**
     * Send the appointment's queue ticket to the clinic's Bluetooth printer:
     * an FCM data message picked up by staff mobile apps with a printer connected.
     */
    public function printTicket(Request $request, Appointment $appointment): JsonResponse
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        try {
            PrintQueueTicketNotification::sendToClinicStaff($appointment);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['ok' => false], 500);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Whether a staff app with a Bluetooth ticket printer is currently online
     * for this clinic. Polled by the dashboard so the front desk can see at a
     * glance whether check-in tickets will auto-print.
     */
    public function printerStatus(Request $request): JsonResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        return response()->json([
            'connected' => $clinic->hasConnectedPrinter(),
            'last_seen' => $clinic->printer_connected_at?->diffForHumans(),
        ]);
    }

    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);

        $selectedDate = Carbon::hasFormat((string) $request->query('date'), 'Y-m-d')
            ? Carbon::createFromFormat('Y-m-d', $request->query('date'))->startOfDay()
            : Carbon::today();

        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', $selectedDate)
            ->with(['client', 'prescriptions'])
            ->orderBy('queue_number')
            ->orderBy('scheduled_at')
            ->get();

        $current = $appointments->firstWhere('status', 'under_examination');

        $waiting = $appointments->where('status', 'scheduled')->values();
        $next = $waiting->get(0);
        $nextNext = $waiting->get(1);

        $stats = [
            'total' => $appointments->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'waiting' => $waiting->count(),
        ];

        // Bookings that came from the rosheta platform and still need the front
        // desk to pull them into today's queue (unified DB: no external API).
        $pendingRequests = Appointment::where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('client')
            ->orderBy('scheduled_at')
            ->get();

        return view('clinic.assistant.dashboard', [
            'doctor' => $doctor,
            'clinic' => $clinic,
            'appointments' => $appointments,
            'current' => $current,
            'next' => $next,
            'nextNext' => $nextNext,
            'stats' => $stats,
            'selectedDate' => $selectedDate,
            'calendar' => $this->buildCalendar($request, $doctor, $clinic, $selectedDate),
            'patients' => Client::orderBy('name')->get(['id', 'name', 'phone_number']),
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /** Confirm a rosheta-platform booking into today's clinic queue. */
    public function confirmPending(Request $request): RedirectResponse|JsonResponse
    {
        $appointment = $this->ownedPending($request);
        $appointment->update(['status' => 'scheduled']);

        return $this->pendingResponse($request, $appointment, __('app.mostashfa.confirmed'));
    }

    /** Decline a rosheta-platform booking. */
    public function cancelPending(Request $request): RedirectResponse|JsonResponse
    {
        $appointment = $this->ownedPending($request);
        $appointment->update(['status' => 'cancelled']);

        return $this->pendingResponse($request, $appointment, __('app.mostashfa.declined'));
    }

    /**
     * Return JSON (with the remaining pending count) for AJAX callers so the
     * dashboard can drop the row in place, or fall back to a redirect.
     */
    protected function pendingResponse(Request $request, Appointment $appointment, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            $remaining = Appointment::where('doctor_id', $appointment->doctor_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            return response()->json(['message' => $message, 'remaining' => $remaining]);
        }

        return back()->with('status', $message);
    }

    protected function ownedPending(Request $request): Appointment
    {
        $doctor = $this->clinicDoctor($request);
        $id = $request->validate(['appointment_id' => ['required', 'integer']])['appointment_id'];

        return Appointment::where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->findOrFail($id);
    }
}
