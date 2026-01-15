<?php

namespace App\Notifications;

use App\Models\NurseOffer;
use App\Notifications\BaseNotification;

class NurseOfferCreatedNotification extends BaseNotification
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
        return 'عرض تمريض جديد';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'New Nurse Offer';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $nurseName = $this->offer->nurse->user->name ?? 'ممرض';
        $totalPrice = number_format($this->offer->total_price ?? 0, 2);
        $visitsCount = $this->offer->visits_count ?? 1;
        
        return "تلقيت عرضاً جديداً من {$nurseName}. السعر الإجمالي: {$totalPrice} ج.م ({$visitsCount} زيارة)";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $nurseName = $this->offer->nurse->user->name ?? 'a nurse';
        $totalPrice = number_format($this->offer->total_price ?? 0, 2);
        $visitsCount = $this->offer->visits_count ?? 1;
        $visitsText = $visitsCount === 1 ? 'visit' : 'visits';
        
        return "You have received a new offer from {$nurseName}. Total price: {$totalPrice} EGP ({$visitsCount} {$visitsText})";
    }

    /**
     * Ensure required relationships are loaded.
     */
    private function ensureRelationsLoaded(): void
    {
        if (!$this->offer->relationLoaded('nurse')) {
            $this->offer->load('nurse.user');
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
            return route('client.nurse-requests.index');
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
            'type' => 'nurse_offer_created',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->home_nurse_request_id,
            'nurse_id' => $this->offer->nurse_id,
            'nurse_name' => $this->offer->nurse->user->name ?? 'Nurse',
            'total_price' => $this->offer->total_price,
            'visits_count' => $this->offer->visits_count,
            'action' => 'view_nurse_offers',
        ];
    }

    /**
     * Get additional data for database notification.
     */
    protected function getNotificationData(): array
    {
        $this->ensureRelationsLoaded();
        
        return [
            'type' => 'nurse_offer_created',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->home_nurse_request_id,
            'nurse_id' => $this->offer->nurse_id,
            'nurse_name' => $this->offer->nurse->user->name ?? 'Nurse',
            'total_price' => $this->offer->total_price,
            'visits_count' => $this->offer->visits_count,
        ];
    }
}
