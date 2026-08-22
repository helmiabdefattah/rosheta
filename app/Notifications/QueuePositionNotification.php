<?php

namespace App\Notifications;

/**
 * Tells a waiting patient how many reservations are ahead of them in today's
 * clinic queue. The number is capped by the clinic's notify_queue_max: once
 * more than that many are waiting, the message reads "more than N patients
 * before you" instead of an exact (and discouraging) large number.
 *
 * Carries both languages so each patient sees it in their own locale; sent as
 * an FCM push and stored as an in-app notification.
 */
class QueuePositionNotification extends BaseNotification
{
    protected bool $sendPush = true;

    protected bool $storeInDatabase = true;

    public function __construct(
        public int $ahead,
        public int $max,
        public ?string $clinicName = null,
    ) {
    }

    /** The position sentence in the given locale, applying the cap. */
    protected function messageFor(string $locale): string
    {
        if ($this->ahead <= 0) {
            return __('app.notify.queue_next', [], $locale);
        }

        if ($this->ahead > $this->max) {
            return __('app.notify.queue_more', ['max' => $this->max], $locale);
        }

        return trans_choice('app.notify.queue_count', $this->ahead, ['count' => $this->ahead], $locale);
    }

    protected function getTitleAr(): string
    {
        return __('app.notify.queue_title', [], 'ar');
    }

    protected function getTitleEn(): string
    {
        return __('app.notify.queue_title', [], 'en');
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
            'type' => 'queue_position',
            // Capped value the app can show as a badge; max+1 flags "more than".
            'ahead' => (string) min($this->ahead, $this->max + 1),
            'max' => (string) $this->max,
            'clinic_name' => (string) $this->clinicName,
        ];
    }

    protected function getNotificationData(): array
    {
        return [
            'type' => 'queue_position',
            'ahead' => $this->ahead,
            'max' => $this->max,
            'clinic_name' => $this->clinicName,
        ];
    }
}
