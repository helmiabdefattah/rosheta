<?php

namespace App\Notifications;

use App\Models\NurseVisit;
use App\Notifications\BaseNotification;

class NurseVisitStatusUpdatedNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public NurseVisit $visit,
        public string $oldStatus,
        public string $newStatus
    ) {
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        return 'تحديث حالة الزيارة';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'Visit Status Updated';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $statusMessages = [
            'scheduled' => 'مجدول',
            'completed' => 'مكتمل',
            'missed' => 'فاتت',
            'cancelled' => 'ملغى',
        ];

        $nurseName = $this->visit->nurse->user->name ?? 'الممرض';
        $newStatusText = $statusMessages[$this->newStatus] ?? $this->newStatus;
        $visitDate = $this->visit->visit_datetime ? $this->visit->visit_datetime->format('Y-m-d H:i') : '';
        
        return "تم تحديث حالة زيارتك من {$nurseName} إلى: {$newStatusText}" . ($visitDate ? " (تاريخ الزيارة: {$visitDate})" : '');
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $statusMessages = [
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'missed' => 'Missed',
            'cancelled' => 'Cancelled',
        ];

        $nurseName = $this->visit->nurse->user->name ?? 'the nurse';
        $newStatusText = $statusMessages[$this->newStatus] ?? $this->newStatus;
        $visitDate = $this->visit->visit_datetime ? $this->visit->visit_datetime->format('Y-m-d H:i') : '';
        
        return "Your visit status from {$nurseName} has been updated to: {$newStatusText}" . ($visitDate ? " (Visit date: {$visitDate})" : '');
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
            'type' => 'nurse_visit_status_updated',
            'visit_id' => $this->visit->id,
            'request_id' => $this->visit->home_nurse_request_id,
            'nurse_id' => $this->visit->nurse_id,
            'nurse_name' => $this->visit->nurse->user->name ?? 'Nurse',
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
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
            'type' => 'nurse_visit_status_updated',
            'visit_id' => $this->visit->id,
            'request_id' => $this->visit->home_nurse_request_id,
            'nurse_id' => $this->visit->nurse_id,
            'nurse_name' => $this->visit->nurse->user->name ?? 'Nurse',
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
