<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\BaseNotification;

class OrderPaidNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public Order $order
    ) {
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        return 'تم دفع الطلب';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'Order Payment Confirmed';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $pharmacyName = $this->order->pharmacy->name ?? 'الصيدلية';
        $totalPrice = number_format($this->order->total_price ?? 0, 2);
        
        return "تم تأكيد دفع طلبك من {$pharmacyName}. المبلغ المدفوع: {$totalPrice} ج.م";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $pharmacyName = $this->order->pharmacy->name ?? 'the pharmacy';
        $totalPrice = number_format($this->order->total_price ?? 0, 2);
        
        return "Your order payment from {$pharmacyName} has been confirmed. Amount paid: {$totalPrice} EGP";
    }

    /**
     * Ensure required relationships are loaded.
     */
    private function ensureRelationsLoaded(): void
    {
        if (!$this->order->relationLoaded('pharmacy')) {
            $this->order->load('pharmacy');
        }
        if (!$this->order->relationLoaded('request')) {
            $this->order->load('request');
        }
    }

    /**
     * Get the URL for the notification.
     */
    protected function getUrl(): ?string
    {
        try {
            return route('client.orders.index');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get additional data for FCM notification.
     */
    protected function getFcmData(): array
    {
        $this->ensureRelationsLoaded();
        
        return [
            'type' => 'order_paid',
            'order_id' => $this->order->id,
            'request_id' => $this->order->client_request_id,
            'pharmacy_id' => $this->order->pharmacy_id,
            'pharmacy_name' => $this->order->pharmacy->name ?? 'Pharmacy',
            'total_price' => $this->order->total_price,
            'action' => 'view_order',
        ];
    }

    /**
     * Get additional data for database notification.
     */
    protected function getNotificationData(): array
    {
        $this->ensureRelationsLoaded();
        
        return [
            'type' => 'order_paid',
            'order_id' => $this->order->id,
            'request_id' => $this->order->client_request_id,
            'pharmacy_id' => $this->order->pharmacy_id,
            'pharmacy_name' => $this->order->pharmacy->name ?? 'Pharmacy',
            'total_price' => $this->order->total_price,
        ];
    }
}
