<?php

namespace App\Notifications;

use App\Models\Offer;
use App\Notifications\BaseNotification;

class OfferVendorStatusUpdatedNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public Offer $offer,
        public string $vendorStatus
    ) {
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        return 'تحديث حالة العرض';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'Offer Status Updated';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $statusMessages = [
            'sample_collected' => 'تم جمع العينة',
            'test_completed' => 'تم إكمال الفحص',
        ];

        $laboratoryName = $this->offer->laboratory->name ?? 'المعمل';
        $statusText = $statusMessages[$this->vendorStatus] ?? $this->vendorStatus;
        
        return "تم تحديث حالة عرضك من {$laboratoryName} إلى: {$statusText}";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $statusMessages = [
            'sample_collected' => 'Sample Collected',
            'test_completed' => 'Test Completed',
        ];

        $laboratoryName = $this->offer->laboratory->name ?? 'the laboratory';
        $statusText = $statusMessages[$this->vendorStatus] ?? $this->vendorStatus;
        
        return "Your offer status from {$laboratoryName} has been updated to: {$statusText}";
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
            'type' => 'offer_vendor_status_updated',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'laboratory_id' => $this->offer->laboratory_id,
            'laboratory_name' => $this->offer->laboratory->name ?? 'Laboratory',
            'vendor_status' => $this->vendorStatus,
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
            'type' => 'offer_vendor_status_updated',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'laboratory_id' => $this->offer->laboratory_id,
            'laboratory_name' => $this->offer->laboratory->name ?? 'Laboratory',
            'vendor_status' => $this->vendorStatus,
        ];
    }
}
