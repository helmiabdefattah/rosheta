<?php

namespace App\Notifications;

use App\Models\NurseOffer;
use App\Notifications\BaseNotification;

class NurseOfferRejectedNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public NurseOffer $offer
    ) {
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        return 'تم رفض عرضك';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'Your Offer Has Been Rejected';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $clientName = $this->offer->request->client->name ?? 'عميل';
        
        return "تم رفض عرضك من قبل {$clientName}";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $clientName = $this->offer->request->client->name ?? 'a client';
        
        return "Your offer has been rejected by {$clientName}";
    }

    /**
     * Ensure required relationships are loaded.
     */
    private function ensureRelationsLoaded(): void
    {
        if (!$this->offer->relationLoaded('request')) {
            $this->offer->load('request.client');
        }
    }

    /**
     * Get the URL for the notification.
     */
    protected function getUrl(): ?string
    {
        try {
            return route('nurse.offers.index');
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
            'type' => 'nurse_offer_rejected',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->home_nurse_request_id,
            'client_name' => $this->offer->request->client->name ?? 'Client',
            'action' => 'view_offers',
        ];
    }

    /**
     * Get additional data for database notification.
     */
    protected function getNotificationData(): array
    {
        $this->ensureRelationsLoaded();
        
        return [
            'type' => 'nurse_offer_rejected',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->home_nurse_request_id,
            'client_name' => $this->offer->request->client->name ?? 'Client',
        ];
    }
}
