<?php

namespace App\Notifications;

use App\Notifications\BaseNotification;
use App\Models\ClientRequest;
use App\Models\HomeNurseRequest;

class NewClientRequestNotification extends BaseNotification
{
    protected $requestModel;
    protected $isHomeNurse;
    protected bool $sendPush = true;

    /**
     * Create a new notification instance.
     * 
     * @param ClientRequest|HomeNurseRequest $requestModel
     */
    public function __construct($requestModel)
    {
        $this->requestModel = $requestModel;
        $this->isHomeNurse = $requestModel instanceof HomeNurseRequest;
    }

    /**
     * Get the notification title in Arabic.
     */
    protected function getTitleAr(): string
    {
        if ($this->isHomeNurse) {
            return 'طلب تمريض منزلي جديد';
        }

        $type = $this->requestModel->type;
        if ($type === 'medicine') {
            return 'طلب دواء جديد';
        } elseif ($type === 'test') {
            return 'طلب فحص طبي جديد';
        } elseif ($type === 'radiology') {
            return 'طلب أشعة جديد';
        }

        return 'طلب جديد من عميل';
    }

    /**
     * Get the notification title in English.
     */
    protected function getTitleEn(): string
    {
        if ($this->isHomeNurse) {
            return 'New Home Nurse Request';
        }

        $type = $this->requestModel->type;
        if ($type === 'medicine') {
            return 'New Medicine Request';
        } elseif ($type === 'test') {
            return 'New Medical Test Request';
        } elseif ($type === 'radiology') {
            return 'New Radiology Request';
        }

        return 'New Client Request';
    }

    /**
     * Get the notification message in Arabic.
     */
    protected function getMessageAr(): string
    {
        $clientName = $this->requestModel->client->name ?? 'عميل';
        if ($this->isHomeNurse) {
            return "هناك طلب تمريض منزلي جديد من {$clientName}";
        }

        return "هناك طلب جديد من {$clientName} بانتظار عرضك";
    }

    /**
     * Get the notification message in English.
     */
    protected function getMessageEn(): string
    {
        $clientName = $this->requestModel->client->name ?? 'a client';
        if ($this->isHomeNurse) {
            return "A new home nurse request has been created by {$clientName}";
        }

        return "A new request from {$clientName} is waiting for your offer";
    }

    /**
     * Get the URL for the notification.
     */
    protected function getUrl(): ?string
    {
        if ($this->isHomeNurse) {
            return route('nurse.dashboard'); // Nurses see requests on dashboard or index
        }

        $type = $this->requestModel->type;
        if ($type === 'medicine') {
             return route('pharmacies.requests.index');
        } else {
             return route('laboratories.requests.index');
        }
    }

    /**
     * Get additional data for the notification.
     */
    protected function getNotificationData(): array
    {
        return [
            'request_id' => $this->requestModel->id,
            'type' => $this->isHomeNurse ? 'nurse_request' : $this->requestModel->type . '_request',
            'client_name' => $this->requestModel->client->name ?? 'N/A',
        ];
    }
}
