<?php

namespace App\Notifications;

use App\Models\Appointment;

/**
 * Sent to a patient the moment they record a reservation / check in at the
 * clinic: it carries their queue number and how many reservations are still
 * ahead of them (capped by the clinic's notify_queue_max, same as the on-demand
 * queue-position push). FCM push + stored in-app notification, in the patient's
 * own language.
 */
class QueueReservationNotification extends BaseNotification
{
    protected bool $sendPush = true;

    protected bool $storeInDatabase = true;

    public function __construct(
        public int $queueNumber,
        public int $ahead,
        public int $max,
        public ?string $clinicName = null,
    ) {
    }

    /**
     * Notify the appointment's patient of their number + how many are ahead.
     * Best-effort and safe to call for any freshly-booked clinic appointment.
     */
    public static function sendTo(Appointment $appointment): void
    {
        $appointment->loadMissing(['client', 'clinic']);

        $client = $appointment->client;
        $number = (int) $appointment->queue_number;
        if (! $client || $number <= 0) {
            return;
        }

        $max = $appointment->clinic?->notifyQueueMax() ?? 10;

        $client->notify(new self(
            $number,
            $appointment->patientsWaitingAhead(),
            $max,
            $appointment->clinic?->name,
        ));
    }

    /** The "ahead of you" clause in the given locale, applying the cap. */
    protected function aheadClause(string $locale): string
    {
        if ($this->ahead <= 0) {
            return __('app.notify.reservation_next', [], $locale);
        }

        if ($this->ahead > $this->max) {
            return __('app.notify.queue_more', ['max' => $this->max], $locale);
        }

        return trans_choice('app.notify.queue_count', $this->ahead, ['count' => $this->ahead], $locale);
    }

    protected function messageFor(string $locale): string
    {
        return __('app.notify.reservation_number', ['number' => $this->queueNumber], $locale)
            .' — '.$this->aheadClause($locale);
    }

    protected function getTitleAr(): string
    {
        return __('app.notify.reservation_title', [], 'ar');
    }

    protected function getTitleEn(): string
    {
        return __('app.notify.reservation_title', [], 'en');
    }

    protected function getMessageAr(): string
    {
        return $this->messageFor('ar');
    }

    protected function getMessageEn(): string
    {
        return $this->messageFor('en');
    }

    protected function getFcmData(): array
    {
        return [
            'type' => 'queue_reservation',
            'queue_number' => (string) $this->queueNumber,
            'ahead' => (string) min($this->ahead, $this->max + 1),
            'clinic_name' => (string) $this->clinicName,
        ];
    }

    protected function getNotificationData(): array
    {
        return [
            'type' => 'queue_reservation',
            'queue_number' => $this->queueNumber,
            'ahead' => $this->ahead,
            'clinic_name' => $this->clinicName,
        ];
    }
}
