<?php

namespace App\Notifications;

/**
 * Example notification class showing how to extend BaseNotification.
 * 
 * To use:
 * 1. Copy this file and rename it
 * 2. Override the protected properties to enable channels
 * 3. Implement the abstract methods
 * 4. Optionally override other methods for customization
 */
class ExampleNotification extends BaseNotification
{
    // Enable email notifications
    protected bool $sendMail = true;
    
    // Enable SMS notifications
    protected bool $sendSms = false;
    
    // Enable push notifications
    protected bool $sendPush = true;
    
    // Database storage is enabled by default
    // To disable: protected bool $storeInDatabase = false;

    /**
     * Get the notification title.
     */
    protected function getTitleAr(): string
    {
        return 'إشعار مثال';
    }

    protected function getTitleEn(): string
    {
        return 'Example Notification';
    }

    /**
     * Get the notification message.
     */
    protected function getMessageAr(): string
    {
        return 'هذا هو نص إشعار مثال.';
    }

    protected function getMessageEn(): string
    {
        return 'This is an example notification message.';
    }

    /**
     * Override email subject if needed.
     */
    protected function getSubject(): string
    {
        return 'Custom Email Subject';
    }

    /**
     * Add custom data for FCM notification.
     */
    protected function getFcmData(): array
    {
        return [
            'type' => 'example',
            'action' => 'view',
            'url' => '/example',
        ];
    }

    /**
     * Add custom data for database notification.
     */
    protected function getNotificationData(): array
    {
        return [
            'type' => 'example',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

