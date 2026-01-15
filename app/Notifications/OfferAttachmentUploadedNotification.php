<?php

namespace App\Notifications;

use App\Models\Offer;
use App\Models\Attachment;
use App\Notifications\BaseNotification;

class OfferAttachmentUploadedNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public Offer $offer,
        public Attachment $attachment
    ) {
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        return 'تم رفع مرفق جديد';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'New Attachment Uploaded';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $laboratoryName = $this->offer->laboratory->name ?? 'المعمل';
        $fileName = $this->attachment->file_name;
        
        return "تم رفع مرفق جديد من {$laboratoryName} لعرضك: {$fileName}";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $laboratoryName = $this->offer->laboratory->name ?? 'the laboratory';
        $fileName = $this->attachment->file_name;
        
        return "A new attachment has been uploaded from {$laboratoryName} for your offer: {$fileName}";
    }

    /**
     * Ensure required relationships are loaded.
     */
    private function ensureRelationsLoaded(): void
    {
        if (!$this->offer->relationLoaded('laboratory')) {
            $this->offer->load('laboratory');
        }
        if (!$this->offer->relationLoaded('request')) {
            $this->offer->load('request');
        }
    }

    /**
     * Get the URL for the notification.
     */
    protected function getUrl(): ?string
    {
        try {
            return route('client.test-results.index');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get additional data for FCM notification.
     */
    protected function getFcmData(): array
    {
        $this->ensureRelationsLoaded();
        
        return [
            'type' => 'offer_attachment_uploaded',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'laboratory_id' => $this->offer->laboratory_id,
            'laboratory_name' => $this->offer->laboratory->name ?? 'Laboratory',
            'attachment_id' => $this->attachment->id,
            'attachment_name' => $this->attachment->file_name,
            'attachment_url' => $this->attachment->url,
            'action' => 'view_test_results',
        ];
    }

    /**
     * Get additional data for database notification.
     */
    protected function getNotificationData(): array
    {
        $this->ensureRelationsLoaded();
        
        return [
            'type' => 'offer_attachment_uploaded',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'laboratory_id' => $this->offer->laboratory_id,
            'laboratory_name' => $this->offer->laboratory->name ?? 'Laboratory',
            'attachment_id' => $this->attachment->id,
            'attachment_name' => $this->attachment->file_name,
        ];
    }
}
