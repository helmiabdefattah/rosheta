<?php

namespace App\Notifications;

use App\Models\Quote;
use App\Notifications\BaseNotification;

class QuoteReceivedNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public Quote $quote
    ) {
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        return 'استفسار جديد';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'New Quote Received';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $clientName = $this->quote->client->name ?? 'عميل';
        $quotableName = $this->getQuotableName();
        
        return "تلقيت استفساراً جديداً من {$clientName}";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $clientName = $this->quote->client->name ?? 'a client';
        
        return "You have received a new quote from {$clientName}";
    }

    /**
     * Ensure required relationships are loaded.
     */
    private function ensureRelationsLoaded(): void
    {
        if (!$this->quote->relationLoaded('client')) {
            $this->quote->load('client');
        }
        if (!$this->quote->relationLoaded('quotable')) {
            $this->quote->load('quotable');
        }
    }

    /**
     * Get the quotable name (Laboratory or Pharmacy).
     */
    private function getQuotableName(): string
    {
        if ($this->quote->quotable) {
            return $this->quote->quotable->name ?? 'Unknown';
        }
        return 'Unknown';
    }

    /**
     * Get the URL for the notification.
     */
    protected function getUrl(): ?string
    {
        try {
            if ($this->quote->model_type === \App\Models\Laboratory::class) {
                return route('laboratories.quotes.index');
            } elseif ($this->quote->model_type === \App\Models\Pharmacy::class) {
                // Add pharmacy quotes route if it exists
                return null;
            }
            return null;
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
            'type' => 'quote_received',
            'quote_id' => $this->quote->id,
            'client_id' => $this->quote->client_id,
            'client_name' => $this->quote->client->name ?? 'Client',
            'model_type' => $this->quote->model_type,
            'model_id' => $this->quote->model_id,
            'action' => 'view_quotes',
        ];
    }

    /**
     * Get additional data for database notification.
     */
    protected function getNotificationData(): array
    {
        $this->ensureRelationsLoaded();
        
        return [
            'type' => 'quote_received',
            'quote_id' => $this->quote->id,
            'client_id' => $this->quote->client_id,
            'client_name' => $this->quote->client->name ?? 'Client',
            'model_type' => $this->quote->model_type,
            'model_id' => $this->quote->model_id,
        ];
    }
}
