<?php

namespace App\Notifications;

use App\Models\Appointment;
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

        return [
            'type' => 'print_ticket',
            'appointment_id' => (string) $a->id,
            'clinic_id' => (string) $a->clinic_id,
            'ticket_number' => (string) $a->queue_number,
            'clinic_name' => (string) ($a->clinic->name ?? ''),
            'doctor_name' => (string) ($a->doctor->name ?? ''),
            'patient_name' => (string) ($a->client->name ?? ''),
            'visit_type' => (string) $a->typeLabel($lang),
            'printer_language' => (string) $lang,
            'time' => optional($a->scheduled_at)->format('Y-m-d H:i') ?? '',
            // Whether to render the QR code on the printed paper (per-clinic
            // toggle) and how many patients are still waiting ahead of this one.
            'print_qr' => $withQr ? '1' : '0',
            'patients_ahead' => (string) $ahead,
            // Every printed label pre-localized to the clinic's printer language,
            // so an app that renders the ticket from fields (its Arabic text path
            // already works) can print correct labels without its own i18n — it
            // just prints these strings and honours print_qr. Sent as JSON.
            'labels' => [
                'queue_number' => __('app.ticket.queue_number', [], $lang),
                'patients_ahead' => trans_choice('app.ticket.patients_ahead', $ahead, ['count' => $ahead], $lang),
                'patient' => __('app.ticket.patient', [], $lang),
                'type' => __('app.ticket.type', [], $lang),
                'date' => __('app.ticket.date', [], $lang),
                'time' => __('app.ticket.time', [], $lang),
                'thanks' => __('app.ticket.thanks', [], $lang),
            ],
            // Ready-to-print ESC/POS buffer (base64). The app writes these bytes
            // to the RONGTA printer verbatim — language and QR are already baked
            // in server-side, so the ticket no longer depends on the app's own
            // rendering or device locale. Fields above remain for older apps.
            'escpos_base64' => EscPosTicketRenderer::make($a, $lang, $withQr)->toBase64(),
        ];
    }
}
