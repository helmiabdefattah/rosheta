<?php

namespace App\Notifications;

use App\Models\Prescription;

/**
 * Data-only FCM message telling the MostashfaOn mobile app to print a
 * prescription on its connected Bluetooth thermal printer (RONGTA RPP30).
 *
 * Sent to every staff user of the prescribing doctor's clinic (doctor +
 * assistants); whichever device is logged in with a printer connected prints
 * it. The whole prescription — patient, medicines, substitutes, doses — travels
 * as data (plus pre-localized labels) and the app renders it as a bitmap so
 * Arabic prints correctly on a head that has no Arabic font.
 */
class PrintPrescriptionNotification extends BaseNotification
{
    protected bool $sendPush = true;

    protected bool $storeInDatabase = false;

    public function __construct(public Prescription $prescription)
    {
    }

    /** Notify all staff (doctor + assistants) of the prescription's clinic. */
    public static function sendToClinicStaff(Prescription $prescription): void
    {
        $prescription->loadMissing([
            'items', 'client', 'diagnosis',
            'doctor.user', 'doctor.assistants', 'doctor.specialization',
            'appointment.clinic', 'appointment.medicalRequests',
        ]);

        $doctor = $prescription->doctor;
        if (! $doctor) {
            return;
        }

        $staff = collect([$doctor->user])
            ->merge($doctor->assistants)
            ->filter()
            ->unique('id');

        foreach ($staff as $user) {
            $user->notify(new self($prescription));
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
        return 'طباعة روشتة';
    }

    protected function getTitleEn(): string
    {
        return 'Print prescription';
    }

    protected function getMessageAr(): string
    {
        return 'روشتة رقم '.$this->prescription->code;
    }

    protected function getMessageEn(): string
    {
        return 'Prescription '.$this->prescription->code;
    }

    protected function getFcmData(): array
    {
        $p = $this->prescription;
        $clinic = $p->appointment?->clinic;

        // Print in the clinic's configured printer language, not the locale of
        // whoever triggered the print.
        $lang = $clinic?->printerLanguage() ?? config('app.locale');

        $client = $p->client;

        $items = $p->items->map(fn ($it) => [
            'name' => (string) $it->medicine_name,
            'dose' => (string) ($it->dose ?? ''),
            'frequency' => (string) ($it->frequency ?? ''),
            'duration' => (string) ($it->duration ?? ''),
            'instructions' => (string) ($it->instructions ?? ''),
            // The alternative carries its own schedule, same shape as above.
            'substitute' => (string) ($it->substitute_name ?? ''),
            'substitute_dose' => (string) ($it->substitute_dose ?? ''),
            'substitute_frequency' => (string) ($it->substitute_frequency ?? ''),
            'substitute_duration' => (string) ($it->substitute_duration ?? ''),
            'substitute_instructions' => (string) ($it->substitute_instructions ?? ''),
        ])->values()->all();

        // Examinations / lab / radiology ordered during the visit, printed under
        // the medicines exactly as they are on the A5 sheet and the PDF.
        $requests = ($p->appointment?->medicalRequests ?? collect())
            ->map(fn ($r) => [
                'type' => (string) __('app.request_types.'.$r->type, [], $lang),
                'name' => (string) $r->name,
                'notes' => (string) ($r->notes ?? ''),
            ])->values()->all();

        return [
            'type' => 'print_prescription',
            'prescription_id' => (string) $p->id,
            'code' => (string) $p->code,
            'clinic_name' => (string) ($clinic?->name ?? config('app.name')),
            'clinic_address' => (string) ($clinic?->address ?? ''),
            'clinic_phone' => (string) ($clinic?->phone_number ?? ''),
            'doctor_name' => (string) ($p->doctor?->name ?? ''),
            'doctor_specialization' => (string) ($p->doctor?->specialization?->name ?? ''),
            'patient_name' => (string) ($client?->name ?? ''),
            'patient_gender' => $client?->gender ? __('app.genders.'.$client->gender, [], $lang) : '',
            'patient_age' => (string) ($client?->age ?? ''),
            'diagnosis' => (string) ($p->diagnosis?->diagnosis ?? ''),
            'notes' => (string) ($p->notes ?? ''),
            'date' => optional($p->created_at)->format('Y-m-d') ?? '',
            'printer_language' => (string) $lang,
            // Medicines (with optional substitute) as JSON — the app renders each
            // as a numbered line, showing the substitute under its medicine.
            'items' => $items,
            'requests' => $requests,
            // The app renders this as a QR on the ticket; sending the URL rather
            // than a bitmap keeps the data message well inside FCM's 4KB limit.
            'landing_url' => \App\Support\LandingQrCode::url(),
            // Pre-localized labels so the app prints a correct-language document
            // without needing its own prescription i18n.
            'labels' => [
                'prescription' => __('app.print.rx_title', [], $lang),
                'doctor' => __('app.print.doctor', [], $lang),
                'patient' => __('app.print.patient', [], $lang),
                'diagnosis' => __('app.common.diagnosis', [], $lang),
                'notes' => __('app.common.notes', [], $lang),
                'substitute' => __('app.print.substitute', [], $lang),
                'dose' => __('app.print.dose', [], $lang),
                'frequency' => __('app.print.frequency', [], $lang),
                'duration' => __('app.print.duration', [], $lang),
                'signature' => __('app.print.signature', [], $lang),
                'yrs' => __('app.common.yrs', [], $lang),
                'instructions' => __('app.print.instructions', [], $lang),
                'requests_title' => __('app.print.requests_title', [], $lang),
                'scan_hint' => __('app.print.scan_hint', [], $lang),
            ],
        ];
    }
}
