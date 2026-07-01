<?php

namespace App\Notifications;

/**
 * A free-form / templated message a clinic pushes to today's patients.
 * Carries both languages so each patient sees it in their own locale.
 * Delivered as an FCM push and stored as an in-app database notification.
 */
class ClinicBroadcastNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $storeInDatabase = true;

    public function __construct(
        public string $titleAr,
        public string $titleEn,
        public string $messageAr,
        public string $messageEn,
        public ?string $clinicName = null,
    ) {
    }

    protected function getTitleAr(): string
    {
        return $this->titleAr;
    }

    protected function getTitleEn(): string
    {
        return $this->titleEn;
    }

    protected function getMessageAr(): string
    {
        return $this->messageAr;
    }

    protected function getMessageEn(): string
    {
        return $this->messageEn;
    }

    protected function getFcmData(): array
    {
        return [
            'type' => 'clinic_broadcast',
            'clinic_name' => (string) $this->clinicName,
        ];
    }

    protected function getNotificationData(): array
    {
        return [
            'type' => 'clinic_broadcast',
            'clinic_name' => $this->clinicName,
        ];
    }
}
