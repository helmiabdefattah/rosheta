<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Notifications\Messages\MailMessage;

class OfferAcceptedNotification extends BaseNotification
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
        $type = $this->offer->request_type === 'medicine' ? 'دواء' : 'فحص طبي';
        return "تم قبول عرضك - {$type}";
    }

    protected function getTitleEn(): string
    {
        $type = $this->offer->request_type === 'medicine' ? 'Medicine' : 'Medical Test';
        return "Your Offer Accepted - {$type}";
    }

    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        $clientName = $this->offer->request->client->name ?? 'عميل';
        $price = number_format($this->offer->total_price ?? 0, 2);
        return "تم قبول عرضك من قبل {$clientName}. السعر الإجمالي: {$price} " . config('app.currency_ar', 'جم');
    }

    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        $clientName = $this->offer->request->client->name ?? 'Client';
        $price = number_format($this->offer->total_price ?? 0, 2);
        return "Your offer has been accepted by {$clientName}. Total price: {$price} " . config('app.currency', 'EGP');
    }

    private function ensureRelationsLoaded(): void
    {
        if (!$this->offer->relationLoaded('request')) {
            $this->offer->load('request.client');
        }
    }

    protected function getSubject(): string
    {
        return app()->getLocale() === 'ar' 
            ? 'تم قبول عرضك' 
            : 'Your Offer Has Been Accepted';
    }

    protected function getUrl(): ?string
    {
        try {
            return $this->offer->request_type === 'medicine' 
                ? route('pharmacies.orders.index')
                : route('laboratories.offers.accepted');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getFcmData(): array
    {
        // Ensure relationships are loaded
        if (!$this->offer->relationLoaded('request')) {
            $this->offer->load('request.client');
        }
        
        return [
            'type' => 'offer_accepted',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'client_name' => $this->offer->request->client->name ?? 'Client',
            'total_price' => $this->offer->total_price,
            'request_type' => $this->offer->request_type,
            'action' => 'view_order',
        ];
    }

    protected function getNotificationData(): array
    {
        return [
            'type' => 'offer_accepted',
            'offer_id' => $this->offer->id,
            'request_id' => $this->offer->client_request_id,
            'client_id' => $this->offer->request->client_id,
            'client_name' => $this->offer->request->client->name ?? 'Client',
            'request_type' => $this->offer->request_type,
            'total_price' => $this->offer->total_price,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $clientName = $this->offer->request->client->name ?? 'Client';
        $price = number_format($this->offer->total_price ?? 0, 2);
        $currency = config('app.currency', 'EGP');
        $type = $this->offer->request_type === 'medicine' 
            ? (app()->getLocale() === 'ar' ? 'دواء' : 'Medicine')
            : (app()->getLocale() === 'ar' ? 'فحص طبي' : 'Medical Test');
        
        if (app()->getLocale() === 'ar') {
            $route = $this->offer->request_type === 'medicine' 
                ? route('pharmacies.orders.index')
                : route('laboratories.offers.accepted');
            
            return (new MailMessage)
                ->subject($this->getSubject())
                ->line("مرحباً {$notifiable->name},")
                ->line("تم قبول عرضك من قبل العميل {$clientName}.")
                ->line("**نوع الطلب:** {$type}")
                ->line("**السعر الإجمالي:** {$price} {$currency}")
                ->action('عرض التفاصيل', $route)
                ->line('يرجى التحضير للطلب.');
        }
        
        $route = $this->offer->request_type === 'medicine' 
            ? route('pharmacies.orders.index')
            : route('laboratories.offers.accepted');
        
        return (new MailMessage)
            ->subject($this->getSubject())
            ->line("Hello {$notifiable->name},")
            ->line("Your offer has been accepted by client {$clientName}.")
            ->line("**Request Type:** {$type}")
            ->line("**Total Price:** {$price} {$currency}")
            ->action('View Details', $route)
            ->line('Please prepare the order.');
    }
}
