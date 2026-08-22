<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Notifications\ClinicBroadcastNotification;
use App\Notifications\QueuePositionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/**
 * Lets clinic staff (doctor or assistant) push a message to every patient
 * who has an appointment today — either a ready-made template (bilingual) or
 * their own free text.
 */
class ClinicNotificationController extends Controller
{
    use ClinicContext;

    public function broadcast(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);

        $data = $request->validate([
            'template' => ['nullable', 'string'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $templates = config('clinic_broadcast.templates', []);
        $key = $data['template'] ?? null;

        // A recognised template ships both languages; free text is sent as-is
        // for both, since we can't translate what the staff typed.
        if ($key && isset($templates[$key])) {
            $messageAr = $templates[$key]['ar'];
            $messageEn = $templates[$key]['en'];
        } else {
            $custom = trim((string) ($data['message'] ?? ''));
            if ($custom === '') {
                return back()->withErrors(['message' => __('app.notify.message_required')]);
            }
            $messageAr = $custom;
            $messageEn = $custom;
        }

        // Every patient with an appointment today (excluding cancelled ones),
        // de-duplicated so a patient with two visits isn't messaged twice.
        $clients = Client::query()
            ->whereHas('appointments', function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)
                    ->whereDate('scheduled_at', today())
                    ->where('status', '!=', 'cancelled');
            })
            ->get();

        if ($clients->isEmpty()) {
            return back()->with('status', __('app.notify.none_today'));
        }

        $title = config('clinic_broadcast.title');
        Notification::send($clients, new ClinicBroadcastNotification(
            $title['ar'],
            $title['en'],
            $messageAr,
            $messageEn,
            $clinic->name,
        ));

        return back()->with('status', __('app.notify.sent', ['count' => $clients->count()]));
    }

    /**
     * Push each still-waiting patient how many reservations are ahead of them in
     * today's queue, capped at the clinic's notify_queue_max. Ordered the same
     * way the queue is (queue number, then time), so a patient's "ahead" count
     * is simply the number of waiting reservations before their own.
     */
    public function queuePosition(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);
        $max = $clinic->notifyQueueMax();

        $waiting = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', today())
            ->where('status', 'scheduled')
            ->with('client')
            ->orderBy('queue_number')
            ->orderBy('scheduled_at')
            ->get();

        $notified = [];
        foreach ($waiting as $index => $appointment) {
            $client = $appointment->client;
            // One message per patient; a patient with two visits keeps the
            // earliest (smaller) position. $index = reservations ahead of this one.
            if (! $client || isset($notified[$client->id])) {
                continue;
            }
            $notified[$client->id] = true;

            $client->notify(new QueuePositionNotification($index, $max, $clinic->name));
        }

        if (empty($notified)) {
            return back()->with('status', __('app.notify.queue_none'));
        }

        return back()->with('status', __('app.notify.queue_sent', ['count' => count($notified)]));
    }
}
