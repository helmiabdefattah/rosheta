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
     */
    public function toFcm($notifiable): array
    {
        $data = $this->getFcmData();
        $url = $this->getUrl();
        
        if ($url) {
            $data['url'] = $url;
            $data['has_url'] = true;
        }

        // Add both languages to data for flexible display
        $data['title_ar'] = $this->getTitleAr();
        $data['title_en'] = $this->getTitleEn();
        $data['message_ar'] = $this->getMessageAr();
        $data['message_en'] = $this->getMessageEn();

        // Determine which language to show based on system locale at time of sending
        // but the SW or client JS will override this based on user preference
        $isAr = app()->getLocale() === 'ar';

        return [
            'title' => $isAr ? $this->getTitleAr() : $this->getTitleEn(),
            'body' => $isAr ? $this->getMessageAr() : $this->getMessageEn(),
            'data' => $data,
        ];
    }

    /**
     * Get the array representation (for database)
     */
    public function toArray($notifiable): array
    {
        $data = $this->getNotificationData();
        $url = $this->getUrl();
        
        if ($url) {
            $data['url'] = $url;
            $data['has_url'] = true;
        }

        return [
            'title_ar' => $this->getTitleAr(),
            'title_en' => $this->getTitleEn(),
            'message_ar' => $this->getMessageAr(),
            'message_en' => $this->getMessageEn(),
            'url' => $url,
            'data' => $data,
        ];
    }

    /**
     * Get localized title based on current app locale
     */
    protected function getTitle(): string
    {
        return app()->getLocale() === 'ar' ? $this->getTitleAr() : $this->getTitleEn();
    }

    /**
     * Get localized message based on current app locale
     */
    protected function getMessage(): string
    {
        return app()->getLocale() === 'ar' ? $this->getMessageAr() : $this->getMessageEn();
    }

    /**
     * Language specific content
     */
    abstract protected function getTitleAr(): string;
    abstract protected function getTitleEn(): string;
    abstract protected function getMessageAr(): string;
    abstract protected function getMessageEn(): string;

    /**
     * Get the redirect URL for the notification.
     * Override in child classes to add a target link.
     */
    protected function getUrl(): ?string
    {
        return null;
    }

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
