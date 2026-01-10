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

    protected function getTitleAr(): string
    {
        return 'عرض جديد على طلبك';
    }

    protected function getTitleEn(): string
    {
        return 'New Offer on Your Request';
    }

    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        $providerName = $this->offer->provider_name ?? 'المزود';
        $price = number_format($this->offer->total_price ?? 0, 2);
        return "تم استلام عرض جديد من {$providerName} بقيمة {$price} " . config('app.currency_ar', 'جم');
    }

    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        $providerName = $this->offer->provider_name ?? 'Provider';
        $price = number_format($this->offer->total_price ?? 0, 2);
        return "You have received a new offer from {$providerName} for {$price} " . config('app.currency', 'EGP');
    }

    private function ensureRelationsLoaded(): void
    {
        if (!$this->offer->relationLoaded('pharmacy') && !$this->offer->relationLoaded('laboratory')) {
            $this->offer->load(['pharmacy', 'laboratory']);
        }
    }

    protected function getSubject(): string
    {
        return app()->getLocale() === 'ar' 
            ? 'عرض جديد على طلبك' 
            : 'New Offer on Your Request';
    }

    protected function getUrl(): ?string
    {
        return url('/client/offers');
    }

    protected function getFcmData(): array
    {
        // Ensure relationships are loaded
        if (!$this->offer->relationLoaded('pharmacy') && !$this->offer->relationLoaded('laboratory')) {
            $this->offer->load(['pharmacy', 'laboratory']);
        }
        
        return [
            'type' => 'offer_created',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'provider_name' => $this->offer->provider_name ?? 'Provider',
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
        // Ensure relationships are loaded
        if (!$this->offer->relationLoaded('pharmacy') && !$this->offer->relationLoaded('laboratory')) {
            $this->offer->load(['pharmacy', 'laboratory']);
        }
        
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
