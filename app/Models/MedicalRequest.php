<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id', 'client_id', 'doctor_id', 'type', 'name', 'status', 'notes',
    ];

    public const TYPES = ['examination', 'lab_test', 'radiology'];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function typeLabel(): string
    {
        $key = 'app.request_types.'.$this->type;

        return __($key) === $key ? ucfirst(str_replace('_', ' ', $this->type)) : __($key);
    }
}
