<?php

namespace App\Notifications\Channels;

use App\Models\Client;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class FcmChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $fcmData = $notification->toFcm($notifiable);
        
        // Get FCM tokens (web and mobile)
        $tokens = [];
        
        if ($notifiable->fcm_token_web) {
            $tokens[] = $notifiable->fcm_token_web;
        }
        
        if ($notifiable->fcm_token_mobile) {
            $tokens[] = $notifiable->fcm_token_mobile;
        }

        if (empty($tokens)) {
            Log::info('No FCM tokens found for notifiable: ' . get_class($notifiable) . ' ID: ' . $notifiable->id);
            return;
        }

        try {
            $messaging = $this->getMessaging();
            
            if (!$messaging) {
                Log::error('Failed to initialize Firebase Messaging. Check service account credentials.');
                return;
            }

            // Send to all tokens
            foreach ($tokens as $token) {
                $this->sendToToken($messaging, $token, $fcmData);
            }
        } catch (\Exception $e) {
            Log::error('FCM notification exception: ' . $e->getMessage(), [
                'notifiable' => get_class($notifiable) . ' ID: ' . $notifiable->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get Firebase Messaging instance.
     *
     * @return \Kreait\Firebase\Messaging|null
     */
    protected function getMessaging()
    {
        try {
            $serviceAccountPath = config('services.fcm.service_account_path');
            
            if (!$serviceAccountPath || !file_exists($serviceAccountPath)) {
                Log::error('Firebase service account file not found: ' . $serviceAccountPath);
                return null;
            }

            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            
            return $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send notification to a specific FCM token.
     *
     * @param  \Kreait\Firebase\Messaging  $messaging
     * @param  string  $token
     * @param  array  $fcmData
     * @return void
     */
    protected function sendToToken($messaging, string $token, array $fcmData): void
    {
        try {
            $title = $fcmData['title'] ?? 'Notification';
            $body = $fcmData['body'] ?? '';
            $data = $fcmData['data'] ?? [];

            // Create Firebase notification
            $firebaseNotification = FirebaseNotification::create($title, $body);

            // Add notification data to the data payload so it's available even when app is in foreground
            $data = array_merge($data, [
                'title' => $title,
                'body' => $body,
                'type' => $data['type'] ?? 'notification',
            ]);

            // Create cloud message
            // Include both notification (for background) and data (for foreground)
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($firebaseNotification)
                ->withData($data);

            // Send the message
            $messaging->send($message);

            Log::info('FCM notification sent successfully', [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title,
            ]);
        } catch (\Kreait\Firebase\Exception\Messaging\InvalidArgument $e) {
            Log::warning('FCM invalid argument: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...',
            ]);
        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            Log::warning('FCM token not found (may be invalid): ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...',
            ]);
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            Log::error('FCM messaging exception: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...',
                'code' => $e->getCode(),
            ]);
        } catch (\Exception $e) {
            Log::error('FCM notification exception: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...',
            ]);
        }
    }
}
