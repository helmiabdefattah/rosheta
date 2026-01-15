<?php

namespace App\Notifications;

use App\Models\NurseVisit;
use App\Notifications\BaseNotification;

class NurseVisitPaidNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public NurseVisit $visit
    ) {
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        return 'تم دفع الزيارة';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'Visit Payment Confirmed';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $nurseName = $this->visit->nurse->user->name ?? 'الممرض';
        $visitPrice = number_format($this->visit->offer->visit_price ?? 0, 2);
        $visitDate = $this->visit->visit_datetime ? $this->visit->visit_datetime->format('Y-m-d H:i') : '';
        
        return "تم تأكيد دفع زيارتك من {$nurseName}. المبلغ المدفوع: {$visitPrice} ج.م" . ($visitDate ? " (تاريخ الزيارة: {$visitDate})" : '');
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $nurseName = $this->visit->nurse->user->name ?? 'the nurse';
        $visitPrice = number_format($this->visit->offer->visit_price ?? 0, 2);
        $visitDate = $this->visit->visit_datetime ? $this->visit->visit_datetime->format('Y-m-d H:i') : '';
        
        return "Your visit payment from {$nurseName} has been confirmed. Amount paid: {$visitPrice} EGP" . ($visitDate ? " (Visit date: {$visitDate})" : '');
    }

    /**
     * Ensure required relationships are loaded.
     */
    private function ensureRelationsLoaded(): void
    {
        if (!$this->visit->relationLoaded('nurse')) {
            $this->visit->load('nurse.user');
        }
        if (!$this->visit->relationLoaded('request')) {
            $this->visit->load('request');
        }
        if (!$this->visit->relationLoaded('offer')) {
            $this->visit->load('offer');
        }
    }

    /**
     * Get the URL for the notification.
     */
    protected function getUrl(): ?string
    {
        try {
            return route('client.nurse-visits.index');
        } catch (\Exception $e) {
            // Fallback to nurse requests if visits route doesn't exist
            try {
                return route('client.nurse-requests.index');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

    /**
     * Get additional data for FCM notification.
     */
    protected function getFcmData(): array
    {
        $this->ensureRelationsLoaded();
        
        return [
            'type' => 'nurse_visit_paid',
            'visit_id' => $this->visit->id,
            'request_id' => $this->visit->home_nurse_request_id,
            'nurse_id' => $this->visit->nurse_id,
            'nurse_name' => $this->visit->nurse->user->name ?? 'Nurse',
            'visit_price' => $this->visit->offer->visit_price ?? 0,
            'visit_datetime' => $this->visit->visit_datetime ? $this->visit->visit_datetime->toIso8601String() : null,
            'action' => 'view_visits',
        ];
    }

    /**
     * Get additional data for database notification.
     */
    protected function getNotificationData(): array
    {
        $this->ensureRelationsLoaded();
        
        return [
            'type' => 'nurse_visit_paid',
            'visit_id' => $this->visit->id,
            'request_id' => $this->visit->home_nurse_request_id,
            'nurse_id' => $this->visit->nurse_id,
            'nurse_name' => $this->visit->nurse->user->name ?? 'Nurse',
            'visit_price' => $this->visit->offer->visit_price ?? 0,
        ];
    }
}
