<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'user_id',
        'client_id',
        'appointment_date',
        'appointment_time',
        'type',
        'price',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'price' => 'decimal:2',
    ];

    public const TYPES = [
        'medical_examination' => 'medical_examination',
        'follow_up' => 'follow_up',
    ];

    public const STATUSES = [
        'pending' => 'pending',
        'confirmed' => 'confirmed',
        'completed' => 'completed',
        'missed' => 'missed',
        'cancelled' => 'cancelled',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
