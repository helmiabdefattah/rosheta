<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\BaseNotification;

class OrderStatusUpdatedNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        return 'تحديث حالة الطلب';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'Order Status Updated';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $statusMessages = [
            'preparing' => 'قيد التحضير',
            'delivering' => 'قيد التوصيل',
            'delivered' => 'تم التوصيل',
        ];

        $pharmacyName = $this->order->pharmacy->name ?? 'الصيدلية';
        $newStatusText = $statusMessages[$this->newStatus] ?? $this->newStatus;
        
        return "تم تحديث حالة طلبك من {$pharmacyName} إلى: {$newStatusText}";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $statusMessages = [
            'preparing' => 'Preparing',
            'delivering' => 'Delivering',
            'delivered' => 'Delivered',
        ];

        $pharmacyName = $this->order->pharmacy->name ?? 'the pharmacy';
        $newStatusText = $statusMessages[$this->newStatus] ?? $this->newStatus;
        
        return "Your order status from {$pharmacyName} has been updated to: {$newStatusText}";
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
            'type' => 'order_status_updated',
            'order_id' => $this->order->id,
            'request_id' => $this->order->client_request_id,
            'pharmacy_id' => $this->order->pharmacy_id,
            'pharmacy_name' => $this->order->pharmacy->name ?? 'Pharmacy',
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
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
            'type' => 'order_status_updated',
            'order_id' => $this->order->id,
            'request_id' => $this->order->client_request_id,
            'pharmacy_id' => $this->order->pharmacy_id,
            'pharmacy_name' => $this->order->pharmacy->name ?? 'Pharmacy',
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
