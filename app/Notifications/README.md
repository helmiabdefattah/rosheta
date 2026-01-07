# Notification System Documentation

## Overview

The notification system provides a base class (`BaseNotification`) that supports multiple notification channels:
- **Email** (optional, default: off)
- **SMS** (optional, default: off)
- **Push Notifications via FCM** (optional, default: off)
- **Database** (always enabled for in-app notifications)

## Setup

### 1. Install Firebase Admin SDK

```bash
composer require kreait/firebase-php
```

### 2. Environment Configuration

Add the following to your `.env` file:

```env
# Firebase Cloud Messaging (FCM) Configuration
# Path to service account JSON file
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json
FCM_PROJECT_ID=your-project-id
```

### 3. Get Service Account Credentials

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Click on the gear icon ⚙️ next to "Project Overview"
4. Select **Project Settings**
5. Go to the **Service Accounts** tab
6. Click **Generate New Private Key**
7. Save the downloaded JSON file to `storage/app/firebase-service-account.json`
8. **IMPORTANT**: Add this file to `.gitignore` to prevent committing sensitive credentials

### 3. Run Migrations

```bash
php artisan migrate
```

This will add `fcm_token_web` and `fcm_token_mobile` columns to both `users` and `clients` tables.

## Creating a Notification

### Basic Example

```php
<?php

namespace App\Notifications;

class OrderShippedNotification extends BaseNotification
{
    // Enable email notifications
    protected bool $sendMail = true;
    
    // Enable push notifications
    protected bool $sendPush = true;
    
    // SMS is disabled by default
    protected bool $sendSms = false;

    protected function getTitle(): string
    {
        return 'Order Shipped';
    }

    protected function getMessage(): string
    {
        return 'Your order has been shipped and will arrive soon.';
    }

    protected function getFcmData(): array
    {
        return [
            'type' => 'order_shipped',
            'order_id' => $this->order->id,
            'action' => 'view_order',
        ];
    }
}
```

### Sending a Notification

```php
use App\Notifications\OrderShippedNotification;

$user->notify(new OrderShippedNotification());
```

## FCM Token Management

### Storing FCM Tokens

#### For Users:
```php
$user->fcm_token_web = $webToken;
$user->fcm_token_mobile = $mobileToken;
$user->save();
```

#### For Clients:
```php
$client->fcm_token_web = $webToken;
$client->fcm_token_mobile = $mobileToken;
$client->save();
```

### API Endpoint Example

You may want to create API endpoints to update FCM tokens:

```php
// routes/api.php
Route::post('/fcm-token', function (Request $request) {
    $request->validate([
        'token_web' => 'nullable|string',
        'token_mobile' => 'nullable|string',
    ]);
    
    $user = auth()->user();
    $user->fcm_token_web = $request->token_web;
    $user->fcm_token_mobile = $request->token_mobile;
    $user->save();
    
    return response()->json(['message' => 'Token updated']);
});
```

## Customization

### Override Email Subject

```php
protected function getSubject(): string
{
    return 'Custom Email Subject';
}
```

### Customize Email Content

```php
public function toMail($notifiable): MailMessage
{
    return (new MailMessage)
        ->subject($this->getSubject())
        ->line('First line')
        ->action('View Order', url('/orders/123'))
        ->line('Thank you for using our service!');
}
```

### Customize FCM Payload

```php
public function toFcm($notifiable): array
{
    return [
        'title' => $this->getTitle(),
        'body' => $this->getMessage(),
        'data' => [
            'type' => 'custom_type',
            'id' => 123,
            'action' => 'open_screen',
        ],
        'image' => 'https://example.com/image.jpg', // Optional
    ];
}
```

### Add Custom Database Data

```php
protected function getNotificationData(): array
{
    return [
        'order_id' => $this->order->id,
        'status' => 'shipped',
        'tracking_number' => 'ABC123',
    ];
}
```

## Channel Configuration

### Enable/Disable Channels

Override the protected properties in your notification class:

```php
protected bool $sendMail = true;   // Enable email
protected bool $sendSms = true;    // Enable SMS (requires SMS provider setup)
protected bool $sendPush = true;   // Enable push notifications
```

### Conditional Channels

You can also override the `via()` method for more complex logic:

```php
public function via($notifiable): array
{
    $channels = ['database'];
    
    if ($notifiable->prefers_email) {
        $channels[] = 'mail';
    }
    
    if ($this->sendPush && $notifiable->fcm_token_mobile) {
        $channels[] = FcmChannel::class;
    }
    
    return $channels;
}
```

## SMS Integration

To enable SMS notifications, you need to:

1. Install an SMS package (e.g., `laravel/vonage-notification-channel`)
2. Configure your SMS provider in `config/services.php`
3. Uncomment and customize the SMS methods in `BaseNotification`
4. Add the SMS channel to the `via()` method

## Notes

- All notifications are stored in the database by default
- FCM notifications are sent to both web and mobile tokens if available
- The FCM channel uses the legacy API by default (simpler setup)
- Notifications are queued by default (can be disabled by removing `ShouldQueue`)
- Failed FCM notifications are logged for debugging

