<?php

namespace App\Notifications;

/**
 * Test notification for sending test messages to clients
 */
class TestNotification extends BaseNotification
{
    protected string $customTitle;
    protected string $customMessage;

    // Enable push notifications
    protected bool $sendPush = true;
    
    // Disable email and SMS for test notifications
    protected bool $sendMail = false;
    protected bool $sendSms = false;

    public function __construct(string $title = null, string $message = null)
    {
        $this->customTitle = $title ?? 'Test Notification';
        $this->customMessage = $message ?? 'This is a test notification from the system.';
    }

    /**
     * Get the notification title.
     */
    protected function getTitle(): string
    {
        return $this->customTitle;
    }

    /**
     * Get the notification message.
     */
    protected function getMessage(): string
    {
        return $this->customMessage;
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

