<?php

namespace App\Notifications;

/**
 * Test notification for sending test messages to clients
 */
class TestNotification extends BaseNotification
{
    protected ?string $customTitle;
    protected ?string $customMessage;

    // Enable push notifications
    protected bool $sendPush = true;
    
    // Disable email and SMS for test notifications
    protected bool $sendMail = false;
    protected bool $sendSms = false;

    public function __construct(?string $title = null, ?string $message = null)
    {
        $this->customTitle = $title;
        $this->customMessage = $message;
    }

    /**
     * Get the notification title.
     */
    protected function getTitleAr(): string
    {
        return $this->customTitle ?: 'إشعار تجريبي';
    }

    protected function getTitleEn(): string
    {
        return $this->customTitle ?: 'Test Notification';
    }

    /**
     * Get the notification message.
     */
    protected function getMessageAr(): string
    {
        return $this->customMessage ?: 'هذا إشعار تجريبي من النظام.';
    }

    protected function getMessageEn(): string
    {
        return $this->customMessage ?: 'This is a test notification from the system.';
    }

    /**
     * Add custom data for FCM notification.
     */
    protected function getFcmData(): array
    {
        return [
            'type' => 'test',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function getUrl(): ?string
    {
        try {
            return route('client.dashboard');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Add custom data for database notification.
     */
    protected function getNotificationData(): array
    {
        return [
            'type' => 'test',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

