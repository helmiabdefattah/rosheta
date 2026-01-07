<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Notifications\Messages\MailMessage;

class OfferCreatedNotification extends BaseNotification
{
    protected bool $sendMail = true;
    protected bool $sendPush = true;
    protected bool $sendSms = false;

    public function __construct(
        public Offer $offer
    ) {
    }

    protected function getTitle(): string
    {
        return app()->getLocale() === 'ar' 
            ? 'عرض جديد على طلبك' 
            : 'New Offer on Your Request';
    }

    protected function getMessage(): string
    {
        $providerName = $this->offer->provider_name ?? 'Provider';
        $price = number_format($this->offer->total_price ?? 0, 2);
        
        if (app()->getLocale() === 'ar') {
            return "تم استلام عرض جديد من {$providerName} بقيمة {$price} " . config('app.currency', 'EGP');
        }
        
        return "You have received a new offer from {$providerName} for {$price} " . config('app.currency', 'EGP');
    }

    protected function getSubject(): string
    {
        return app()->getLocale() === 'ar' 
            ? 'عرض جديد على طلبك' 
            : 'New Offer on Your Request';
    }

    protected function getFcmData(): array
    {
        return [
            'type' => 'offer_created',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'provider_name' => $this->offer->provider_name,
            'total_price' => $this->offer->total_price,
            'action' => 'view_offer',
        ];
    }

    protected function getNotificationData(): array
    {
        return [
            'type' => 'offer_created',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'provider_name' => $this->offer->provider_name,
            'provider_id' => $this->offer->provider_id,
            'request_type' => $this->offer->request_type,
            'total_price' => $this->offer->total_price,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $providerName = $this->offer->provider_name ?? 'Provider';
        $price = number_format($this->offer->total_price ?? 0, 2);
        $currency = config('app.currency', 'EGP');
        
        if (app()->getLocale() === 'ar') {
            return (new MailMessage)
                ->subject($this->getSubject())
                ->line("مرحباً {$notifiable->name},")
                ->line("تم استلام عرض جديد على طلبك.")
                ->line("**المزود:** {$providerName}")
                ->line("**السعر الإجمالي:** {$price} {$currency}")
                ->action('عرض التفاصيل', url('/client/offers'))
                ->line('شكراً لاستخدامك خدماتنا!');
        }
        
        return (new MailMessage)
            ->subject($this->getSubject())
            ->line("Hello {$notifiable->name},")
            ->line("You have received a new offer on your request.")
            ->line("**Provider:** {$providerName}")
            ->line("**Total Price:** {$price} {$currency}")
            ->action('View Details', url('/client/offers'))
            ->line('Thank you for using our services!');
    }
}
