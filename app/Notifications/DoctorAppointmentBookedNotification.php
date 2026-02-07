<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\BaseNotification;
use Carbon\Carbon;

class DoctorAppointmentBookedNotification extends BaseNotification
{
    protected bool $sendPush = true;
    protected bool $sendMail = false;

    public function __construct(
        public Appointment $appointment
    ) {
    }

    /**
     * Ensure required relationships are loaded.
     */
    private function ensureRelationsLoaded(): void
    {
        if (!$this->appointment->relationLoaded('clinic')) {
            $this->appointment->load('clinic');
        }
        if (!$this->appointment->relationLoaded('client')) {
            $this->appointment->load('client');
        }
    }

    protected function getTitleAr(): string
    {
        return 'حجز موعد جديد';
    }

    protected function getTitleEn(): string
    {
        return 'New appointment booking';
    }

    protected function getMessageAr(): string
    {
        $this->ensureRelationsLoaded();
        $clientName = $this->appointment->client?->name ?? 'عميل';
        $clinicName = $this->appointment->clinic?->name ?? 'العيادة';
        $date = $this->appointment->appointment_date instanceof \Carbon\Carbon
            ? $this->appointment->appointment_date->translatedFormat('l j F Y')
            : Carbon::parse($this->appointment->appointment_date)->translatedFormat('l j F Y');
        $time = $this->appointment->appointment_time instanceof \Carbon\Carbon
            ? $this->appointment->appointment_time->format('g:i A')
            : Carbon::parse($this->appointment->appointment_time)->format('g:i A');
        return "{$clientName} حجز موعداً جديداً عندك في {$clinicName} - {$date} الساعة {$time}";
    }

    protected function getMessageEn(): string
    {
        $this->ensureRelationsLoaded();
        $clientName = $this->appointment->client?->name ?? 'A client';
        $clinicName = $this->appointment->clinic?->name ?? 'your clinic';
        $date = $this->appointment->appointment_date instanceof \Carbon\Carbon
            ? $this->appointment->appointment_date->format('l j F Y')
            : Carbon::parse($this->appointment->appointment_date)->format('l j F Y');
        $time = $this->appointment->appointment_time instanceof \Carbon\Carbon
            ? $this->appointment->appointment_time->format('g:i A')
            : Carbon::parse($this->appointment->appointment_time)->format('g:i A');
        return "{$clientName} booked a new appointment at {$clinicName} - {$date} at {$time}";
    }

    protected function getUrl(): ?string
    {
        try {
            return route('doctor.appointments.index');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getFcmData(): array
    {
        $this->ensureRelationsLoaded();
        return [
            'type' => 'doctor_appointment_booked',
            'appointment_id' => $this->appointment->id,
            'clinic_id' => $this->appointment->clinic_id,
            'clinic_name' => $this->appointment->clinic?->name,
            'client_name' => $this->appointment->client?->name,
            'appointment_date' => $this->appointment->appointment_date?->format('Y-m-d'),
            'appointment_time' => $this->appointment->appointment_time instanceof \Carbon\Carbon
                ? $this->appointment->appointment_time->format('H:i')
                : (string) $this->appointment->appointment_time,
        ];
    }

    protected function getNotificationData(): array
    {
        $this->ensureRelationsLoaded();
        return [
            'type' => 'doctor_appointment_booked',
            'appointment_id' => $this->appointment->id,
            'clinic_id' => $this->appointment->clinic_id,
            'clinic_name' => $this->appointment->clinic?->name,
            'client_name' => $this->appointment->client?->name,
            'appointment_date' => $this->appointment->appointment_date?->format('Y-m-d'),
            'appointment_time' => $this->appointment->appointment_time instanceof \Carbon\Carbon
                ? $this->appointment->appointment_time->format('H:i')
                : (string) $this->appointment->appointment_time,
        ];
    }
}
