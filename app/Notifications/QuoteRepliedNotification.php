<?php

namespace App\Notifications;

use App\Models\Quote;
use App\Notifications\BaseNotification;

class QuoteRepliedNotification extends BaseNotification
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
        return 'تم الرد على استفسارك';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        return 'Your Quote Has Been Replied';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        
        $quotableName = $this->getQuotableName();
        
        return "تم الرد على استفسارك من {$quotableName}";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        
        $quotableName = $this->getQuotableName();
        
        return "Your quote has been replied by {$quotableName}";
    }

    /**
     * Ensure required relationships are loaded.
     */
    private function ensureRelationsLoaded(): void
    {
        if (!$this->quote->relationLoaded('quotable')) {
            $this->quote->load('quotable');
        }
        
        // If quotable is still null, try to load it manually
        if (!$this->quote->quotable && $this->quote->model_type && $this->quote->model_id) {
            $modelClass = $this->quote->model_type;
            if (class_exists($modelClass)) {
                $this->quote->setRelation('quotable', $modelClass::find($this->quote->model_id));
            }
        }
    }

    /**
     * Get the quotable name (Laboratory or Pharmacy).
     */
    private function getQuotableName(): string
    {
        $this->ensureRelationsLoaded();
        
        if ($this->quote->quotable) {
            // Try different possible name attributes
            $name = $this->quote->quotable->name 
                ?? $this->quote->quotable->title 
                ?? null;
            
            if ($name) {
                return $name;
            }
        }
        
        // Fallback: try to get name directly from model
        if ($this->quote->model_type && $this->quote->model_id) {
            $modelClass = $this->quote->model_type;
            if (class_exists($modelClass)) {
                $model = $modelClass::find($this->quote->model_id);
                if ($model && isset($model->name)) {
                    return $model->name;
                }
            }
        }
        
        return app()->getLocale() === 'ar' ? 'المعمل' : 'Laboratory';
    }

    /**
     * Get the URL for the notification.
     */
    protected function getUrl(): ?string
    {
        try {
            return route('client.quotes.index');
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
            'type' => 'quote_replied',
            'quote_id' => $this->quote->id,
            'client_id' => $this->quote->client_id,
            'model_type' => $this->quote->model_type,
            'model_id' => $this->quote->model_id,
            'quotable_name' => $this->getQuotableName(),
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
            'type' => 'quote_replied',
            'quote_id' => $this->quote->id,
            'client_id' => $this->quote->client_id,
            'model_type' => $this->quote->model_type,
            'model_id' => $this->quote->model_id,
            'quotable_name' => $this->getQuotableName(),
        ];
    }
}
