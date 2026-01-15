<?php

namespace App\Notifications;

use App\Models\NurseOffer;
use App\Notifications\BaseNotification;

class NurseOfferAcceptedNotification extends BaseNotification
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
        return 'تم قبول عرضك';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'Your Offer Has Been Accepted';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $clientName = $this->offer->request->client->name ?? 'عميل';
        $totalPrice = number_format($this->offer->total_price ?? 0, 2);
        
        return "تم قبول عرضك من قبل {$clientName}. السعر الإجمالي: {$totalPrice} ج.م";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $clientName = $this->offer->request->client->name ?? 'a client';
        $totalPrice = number_format($this->offer->total_price ?? 0, 2);
        
        return "Your offer has been accepted by {$clientName}. Total price: {$totalPrice} EGP";
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
            'type' => 'nurse_offer_accepted',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->home_nurse_request_id,
            'client_name' => $this->offer->request->client->name ?? 'Client',
            'total_price' => $this->offer->total_price,
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
            'type' => 'nurse_offer_accepted',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->home_nurse_request_id,
            'client_name' => $this->offer->request->client->name ?? 'Client',
            'total_price' => $this->offer->total_price,
        ];
    }
}
