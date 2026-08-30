<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Support\SiteBrand;
use App\Support\EscPosTicketRenderer;

/**
 * Data-only FCM message telling the MostashfaOn mobile app to print a queue
 * ticket on its connected Bluetooth printer (RONGTA RPP30).
 *
 * Sent to every staff user of the appointment's clinic (doctor + assistants);
 * whichever device is logged in and has a printer connected prints the ticket.
 * Not stored in the database and carries no visible notification: a data-only
 * high-priority message is required for the Flutter background handler to run.
 */
class PrintQueueTicketNotification extends BaseNotification
{
    /**
     * Headroom under FCM's 4096-byte data cap. The margin absorbs the fields
     * that vary with the visit — long clinic, doctor and patient names.
     */
    private const FCM_SAFE_BYTES = 3400;

    protected bool $sendPush = true;

    protected bool $storeInDatabase = false;

    public function __construct(public Appointment $appointment)
    {
    }

    /** Notify all staff (doctor + assistants) of the appointment's clinic. */
    public static function sendToClinicStaff(Appointment $appointment): void
    {
        $appointment->loadMissing(['client', 'clinic', 'doctor.user', 'doctor.assistants']);

        $doctor = $appointment->doctor;
        if (! $doctor) {
            return;
        }

        $staff = collect([$doctor->user])
            ->merge($doctor->assistants)
            ->filter()
            ->unique('id');

        foreach ($staff as $user) {
            $user->notify(new self($appointment));
        }
    }

    public function toFcm($notifiable): array
    {
        $data = parent::toFcm($notifiable);
        // Data-only + mobile-only + high priority: the app must receive this in
        // the background handler (notification payloads bypass it on Android).
        $data['data_only'] = true;
        $data['mobile_only'] = true;
        $data['priority'] = 'high';

        return $data;
    }

    protected function getTitleAr(): string
    {
        return 'طباعة تذكرة الدور';
    }

    protected function getTitleEn(): string
    {
        return 'Print queue ticket';
    }

    protected function getMessageAr(): string
    {
        return 'تذكرة رقم '.$this->appointment->queue_number;
    }

    protected function getMessageEn(): string
    {
        return 'Ticket #'.$this->appointment->queue_number;
    }

    protected function getFcmData(): array
    {
        $a = $this->appointment;

        // Print in the clinic's configured printer language, not the locale of
        // whoever triggered the print (kiosk patient, assistant, doctor) —
        // otherwise one clinic gets mixed-language tickets. `printer_language`
        // is sent too so the app localises its own printed labels to match.
        $lang = $a->clinic?->printerLanguage() ?? config('app.locale');
        $withQr = (bool) ($a->clinic?->print_qr ?? true);
        $ahead = $a->patientsWaitingAhead();

        $clinicName = (string) ($a->clinic->name ?? '');
        $doctorName = (string) ($a->doctor->name ?? '');
        $patientName = (string) ($a->client->name ?? '');
        $visitType = (string) $a->typeLabel($lang);

        $data = [
            'type' => 'print_ticket',
            'appointment_id' => (string) $a->id,
            'clinic_id' => (string) $a->clinic_id,
            'ticket_number' => (string) $a->queue_number,
            'clinic_name' => $clinicName,
            'doctor_name' => $doctorName,
            'patient_name' => $patientName,
            'visit_type' => $visitType,
            'printer_language' => (string) $lang,
            'time' => optional($a->scheduled_at)->format('Y-m-d H:i') ?? '',
            // Whether to render the QR code on the printed paper (per-clinic
            // toggle) and how many patients are still waiting ahead of this one.
            'print_qr' => $withQr ? '1' : '0',
            'patients_ahead' => (string) $ahead,
            // Every printed label pre-localized to the clinic's printer language,
            // so the app can render the ticket (bitmap path) with correct labels
            // and honour print_qr without needing its own i18n. Sent as JSON.
            'labels' => [
                'queue_number' => __('app.ticket.queue_number', [], $lang),
                'patients_ahead' => trans_choice('app.ticket.patients_ahead', $ahead, ['count' => $ahead], $lang),
                'patient' => __('app.ticket.patient', [], $lang),
                'type' => __('app.ticket.type', [], $lang),
                'date' => __('app.ticket.date', [], $lang),
                'time' => __('app.ticket.time', [], $lang),
                'thanks' => __('app.ticket.thanks', [], $lang),
                // Printed under the platform mark; the app bundles the mark itself.
                'site_name' => SiteBrand::name($lang),
            ],
        ];

        // Fast path — only when every printed string is ASCII. The RONGTA RPP30
        // has no Arabic font, so Arabic can't be printed as ESC/POS text; those
        // tickets fall back to the app's on-device bitmap renderer (which shapes
        // Arabic correctly). When the whole ticket is Latin we send a ready-made
        // ESC/POS buffer the app prints verbatim — far faster than a bitmap.
        if ($lang === 'en'
            && $this->isAscii($clinicName)
            && $this->isAscii($doctorName)
            && $this->isAscii($patientName)
            && $this->isAscii($visitType)
        ) {
            $data['escpos_base64'] = EscPosTicketRenderer::make($a, $lang, $withQr)->toBase64();

            // The ready-made buffer now carries the logo raster, so it is by far
            // the largest field. FCM rejects a data message over 4KB outright —
            // and a rejected message prints nothing at all. If we are close to
            // that, drop the fast path: the app then renders the ticket itself,
            // logo included, with no size ceiling. Slower, but it prints.
            if (self::payloadBytes($data) > self::FCM_SAFE_BYTES) {
                unset($data['escpos_base64']);
            }
        }

        return $data;
    }

    /**
     * Size of the data message as FCM measures it — every key and value, with
     * arrays in the JSON form the channel sends them as.
     */
    private static function payloadBytes(array $data): int
    {
        $bytes = 0;

        foreach ($data as $key => $value) {
            $bytes += strlen((string) $key);
            $bytes += strlen(is_array($value) ? (string) json_encode($value) : (string) $value);
        }

        return $bytes;
    }

    /** True when the string is empty or pure 7-bit ASCII (safe for text-mode ESC/POS). */
    private function isAscii(string $s): bool
    {
        return $s === '' || mb_check_encoding($s, 'ASCII');
    }
}
