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
            Log::info('Notification does not have toFcm method', [
                'notification' => get_class($notification),
            ]);
            return;
        }

        try {
            $fcmData = $notification->toFcm($notifiable);
            
            // Validate FCM data structure
            if (!isset($fcmData['title']) || !isset($fcmData['body'])) {
                Log::warning('FCM data missing title or body', [
                    'notification' => get_class($notification),
                    'notifiable' => get_class($notifiable) . ' ID: ' . $notifiable->id,
                    'fcm_data' => $fcmData,
                ]);
            }
            
            // Get FCM tokens (web and mobile)
            $tokens = [];
            
            if ($notifiable->fcm_token_web) {
                $tokens[] = $notifiable->fcm_token_web;
            }
            
            if ($notifiable->fcm_token_mobile) {
                $tokens[] = $notifiable->fcm_token_mobile;
            }

            if (empty($tokens)) {
                Log::info('No FCM tokens found for notifiable', [
                    'notifiable_type' => get_class($notifiable),
                    'notifiable_id' => $notifiable->id,
                    'notification' => get_class($notification),
                ]);
                return;
            }

            Log::info('Attempting to send FCM notification', [
                'notification' => get_class($notification),
                'notifiable' => get_class($notifiable) . ' ID: ' . $notifiable->id,
                'token_count' => count($tokens),
                'title' => $fcmData['title'] ?? 'N/A',
            ]);

            $messaging = $this->getMessaging();
            
            if (!$messaging) {
                Log::error('Failed to initialize Firebase Messaging. Check service account credentials.', [
                    'notification' => get_class($notification),
                    'notifiable' => get_class($notifiable) . ' ID: ' . $notifiable->id,
                ]);
                return;
            }

            // Send to all tokens
            foreach ($tokens as $token) {
                $this->sendToToken($messaging, $token, $fcmData);
            }
        } catch (\Exception $e) {
            Log::error('FCM notification exception', [
                'notification' => get_class($notification),
                'notifiable' => get_class($notifiable) . ' ID: ' . $notifiable->id,
                'error' => $e->getMessage(),
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
            
            // Ensure we have an absolute path
            if ($serviceAccountPath) {
                // If path is relative (doesn't start with / or drive letter), make it absolute
                if (!str_starts_with($serviceAccountPath, '/') && !preg_match('/^[A-Za-z]:/', $serviceAccountPath)) {
                    // Remove 'storage/' prefix if present and use storage_path()
                    $serviceAccountPath = str_replace('storage/', '', $serviceAccountPath);
                    $serviceAccountPath = storage_path($serviceAccountPath);
                }
            } else {
                // Fallback to default path
                $serviceAccountPath = storage_path('app/firebase-service-account.json');
            }
            
            if (!file_exists($serviceAccountPath)) {
                Log::error('Firebase service account file not found', [
                    'configured_path' => config('services.fcm.service_account_path'),
                    'resolved_path' => $serviceAccountPath,
                    'storage_path' => storage_path('app/firebase-service-account.json'),
                    'file_exists_default' => file_exists(storage_path('app/firebase-service-account.json')),
                ]);
                return null;
            }

            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            
            return $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase', [
                'error' => $e->getMessage(),
                'path' => $serviceAccountPath ?? 'not set',
                'trace' => $e->getTraceAsString(),
            ]);
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

            // Ensure all data values are strings for FCM withData()
            $stringData = array_map(function ($value) {
                return is_array($value) ? json_encode($value) : (string) $value;
            }, $data);

            // Include both notification + data:
            // - Android/iOS native apps need a notification payload to show the system tray when the app is backgrounded or killed.
            // - Data-only messages often deliver without a visible notification on Android.
            // - Web still receives the same data payload for the service worker / foreground handlers.
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($firebaseNotification)
                ->withData($stringData);

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
