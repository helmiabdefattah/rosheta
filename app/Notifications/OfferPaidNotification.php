<?php

namespace App\Notifications;

use App\Models\Offer;
use App\Notifications\BaseNotification;

class OfferPaidNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public Offer $offer
    ) {
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        return 'تم دفع العرض';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'Offer Payment Confirmed';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $laboratoryName = $this->offer->laboratory->name ?? 'المعمل';
        $totalPrice = number_format($this->offer->total_price ?? 0, 2);
        
        return "تم تأكيد دفع عرضك من {$laboratoryName}. المبلغ المدفوع: {$totalPrice} ج.م";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $laboratoryName = $this->offer->laboratory->name ?? 'the laboratory';
        $totalPrice = number_format($this->offer->total_price ?? 0, 2);
        
        return "Your offer payment from {$laboratoryName} has been confirmed. Amount paid: {$totalPrice} EGP";
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
            'type' => 'offer_paid',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'laboratory_id' => $this->offer->laboratory_id,
            'laboratory_name' => $this->offer->laboratory->name ?? 'Laboratory',
            'total_price' => $this->offer->total_price,
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
            'type' => 'offer_paid',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'laboratory_id' => $this->offer->laboratory_id,
            'laboratory_name' => $this->offer->laboratory->name ?? 'Laboratory',
            'total_price' => $this->offer->total_price,
        ];
    }
}
