<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
// use Illuminate\Notifications\Messages\VonageMessage; // Uncomment if using Vonage
use App\Notifications\Channels\FcmChannel;

abstract class BaseNotification extends Notification
{
    use Queueable;

    /**
     * Whether to send email notification.
     * Override in child classes to enable.
     */
    protected bool $sendMail = false;

    /**
     * Whether to send SMS notification.
     * Override in child classes to enable.
     */
    protected bool $sendSms = false;

    /**
     * Whether to send push notification.
     * Override in child classes to enable.
     */
    protected bool $sendPush = false;

    /**
     * Whether to store notification in database.
     * Override in child classes to disable.
     */
    protected bool $storeInDatabase = true;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        $channels = [];
        
        // Add database channel if enabled
        if ($this->storeInDatabase) {
            $channels[] = 'database';
        }

        if ($this->sendMail && $notifiable->email) {
            $channels[] = 'mail';
        }

        if ($this->sendSms && $notifiable->phone_number) {
            // Add your SMS channel here (vonage, twilio, etc.)
            // $channels[] = 'vonage';
        }

        if ($this->sendPush) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     * Override in child classes to customize.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->getSubject())
            ->line($this->getMessage());
    }

    /**
     * Get the SMS representation of the notification.
     * Override in child classes to customize.
     * Uncomment and modify based on your SMS provider.
     *
     * @param  mixed  $notifiable
     * @return mixed
     */
    // public function toVonage($notifiable): VonageMessage
    // {
    //     return (new VonageMessage)
    //         ->content($this->getMessage());
    // }

    /**
     * Get the FCM representation of the notification.
     * Override in child classes to customize.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toFcm($notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'body' => $this->getMessage(),
            'data' => $this->getFcmData(),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'data' => $this->getNotificationData(),
        ];
    }

    /**
     * Get the notification title.
     * Override in child classes.
     */
    abstract protected function getTitle(): string;

    /**
     * Get the notification message.
     * Override in child classes.
     */
    abstract protected function getMessage(): string;

    /**
     * Get the email subject.
     * Override in child classes to customize.
     */
    protected function getSubject(): string
    {
        return $this->getTitle();
    }

    /**
     * Get additional data for FCM notification.
     * Override in child classes to add custom data.
     */
    protected function getFcmData(): array
    {
        return [];
    }

    /**
     * Get additional data for database notification.
     * Override in child classes to add custom data.
     */
    protected function getNotificationData(): array
    {
        return [];
    }
}
